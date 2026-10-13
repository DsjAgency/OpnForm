<template>
  <div class="flex flex-grow mt-6 mb-10">
    <two-factor-verification-modal
      v-if="pendingAuthToken"
      :show="showTwoFactorModal"
      :pending-auth-token="pendingAuthToken"
      @verified="handleTwoFactorVerifiedAndRedirect"
      @cancel="handleTwoFactorCancel"
    />

    <div class="w-full md:w-2/3 md:mx-auto md:max-w-md px-4">
      <div
        v-if="loading || showTwoFactorModal"
        class="m-10"
      >
        <h3 class="my-6 text-center">
          {{ showTwoFactorModal ? 'Verificando seu código...' : isRetrying ? 'Reconectando com segurança...' : 'Concluindo o login...' }}
        </h3>
        <Loader class="h-6 w-6 mx-auto m-10" />
      </div>
      <div
        v-else-if="error"
        class="m-6 flex flex-col items-center space-y-4"
      >
        <UAlert
          icon="i-lucide-alert-triangle"
          color="error"
          variant="subtle"
          class="w-full"
          title="Não foi possível concluir o login"
          :description="error"
        />
        <UButton
          v-if="linkToken"
          color="primary"
          label="Vincular conta existente"
          @click="goToLinkLogin"
        />
        <UButton
          :to="{ name: 'login' }"
          label="Voltar para o login"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { oidcApi } from "~/api"
import { redirectToOidcProvider } from "~/lib/oidc/redirect"
import {
  getOidcRetryAfterSeconds,
  isOidcRateLimitedError,
  oidcRateLimitMessage,
} from "~/lib/oidc/rate-limit"
import {
  canAutomaticallyRetryOidcSignIn,
  clearOidcAutomaticRetry,
  consumeOidcStateVerifier,
  markOidcAutomaticRetry,
  storeOidcStateVerifier,
} from "~/lib/oidc/state-verifier"

const router = useRouter()
const route = useRoute()
const loading = ref(true)
const error = ref(null)
const linkToken = ref(null)
const callbackStarted = ref(false)
const isRetrying = ref(false)
const { startLink } = useOidcLinking()
const authFlow = useAuthFlow()
const { showTwoFactorModal, pendingAuthToken, handleTwoFactorVerified, handleTwoFactorCancel: handleTwoFactorCancelFromFlow, handleTwoFactorError } = authFlow

const authorizationCodeWasAlreadyUsed = (providerError) => {
  return providerError.includes('AADSTS54005') || providerError.includes('Authorization code was already redeemed')
}

const retryOidcSignIn = (slug) => {
  markOidcAutomaticRetry(slug)
  isRetrying.value = true

  return oidcApi.redirect(slug)
    .then((response) => {
      if (!response.redirect_url) {
        return { started: false }
      }

      storeOidcStateVerifier(slug, response.state, response.state_verifier)
      redirectToOidcProvider(response.redirect_url)
      return { started: true }
    })
    .catch((error) => ({ started: false, error }))
}

const handleCallback = async () => {
  if (callbackStarted.value) return

  callbackStarted.value = true
  const slug = route.params.slug
  
  try {
    // Build query params object from route
    const queryParams = {}
    Object.keys(route.query).forEach(key => {
      if (route.query[key]) {
        queryParams[key] = route.query[key]
      }
    })
    const state = Array.isArray(route.query.state) ? route.query.state[0] : route.query.state
    const stateVerifier = consumeOidcStateVerifier(slug, state)
    
    // Call the OIDC callback endpoint to process authorization code
    let response
    try {
      response = await oidcApi.callback(slug, queryParams, stateVerifier)
    } catch (error) {
      // Handle 422 responses that indicate 2FA is required (not validation errors)
      const twoFactorResponse = handleTwoFactorError(error)
      if (twoFactorResponse) {
        response = twoFactorResponse
      } else {
        throw error
      }
    }
    
    // Handle authentication success (handles both 2FA and non-2FA cases)
    // handleAuthSuccess will check for requires_2fa and show modal if needed
    await authFlow.handleAuthSuccess(response, 'oidc', response.new_user)
    clearOidcAutomaticRetry(slug)
    
    // If 2FA modal is shown, don't redirect yet (handled in handleTwoFactorVerifiedAndRedirect)
    if (showTwoFactorModal.value) {
      loading.value = false
      return
    }
    
    // No 2FA required, proceed with redirect
    // Response should contain token and user when 2FA is not required
    if (!response.token || !response.user) {
      throw new Error("Unexpected response format from OIDC callback")
    }
    
    // Redirect based on user status (aligned with OAuth callback pattern)
    if (response.new_user) {
      router.push({ name: "forms-create" })
      useAlert().success("Pronto! Você foi cadastrado via OIDC. Bem-vindo ao OpnForm.")
    } else {
      // For existing users, redirect to intended URL or home (aligned with OAuth)
      router.push({ name: "home" })
      useAlert().success("Login realizado com sucesso!")
    }
  } catch (err) {
    console.error("[OIDC Callback] Authentication error:", err)
    
    const errorResponse = err.response?._data || {}
    const errorMessage = errorResponse.message || err.message || "Falha na autenticação"
    const providerError = [errorResponse.message, errorResponse.error_description, err.message]
      .filter(Boolean)
      .join(' ')
    error.value = 'Não foi possível concluir este login. Volte para a tela de login ou fale com o administrador se o problema continuar.'
    
    // Handle specific error cases
    if (errorResponse.error === 'oidc_account_link_required' && errorResponse.link_token) {
      error.value = 'Já existe uma conta com este e-mail. Vincule sua conta existente para continuar.'
      linkToken.value = errorResponse.link_token
    } else if (isOidcRateLimitedError(err)) {
      error.value = oidcRateLimitMessage(getOidcRetryAfterSeconds(err))
    } else if (authorizationCodeWasAlreadyUsed(providerError)) {
      if (canAutomaticallyRetryOidcSignIn(slug)) {
        return retryOidcSignIn(slug)
          .then(({ started, error: retryError }) => {
            if (started) return

            isRetrying.value = false
            error.value = isOidcRateLimitedError(retryError)
              ? oidcRateLimitMessage(getOidcRetryAfterSeconds(retryError))
              : 'Não foi possível reconectar automaticamente. Volte para a tela de login e tente de novo, ou fale com o administrador se o problema continuar.'
            loading.value = false
          })
      }

      error.value = 'Não foi possível reconectar automaticamente. Volte para a tela de login e tente de novo, ou fale com o administrador se o problema continuar.'
    } else if (errorMessage.includes('more than 2 users')) {
      error.value = 'Esta instância auto-hospedada está limitada a 2 usuários sem licença Enterprise. Peça ao administrador para ativar uma licença ou remover outro usuário.'
    } else if (errorMessage.includes('account with this email already exists')) {
      error.value = 'Já existe uma conta com este e-mail. Fale com o administrador para vincular suas contas.'
    } else if (errorMessage.includes('blocked')) {
      error.value = 'Sua conta foi bloqueada. Fale com o suporte.'
    }
    
    loading.value = false
  }
}

const handleTwoFactorCancel = () => {
  handleTwoFactorCancelFromFlow()
  router.push({ name: 'login' })
}

const goToLinkLogin = () => {
  startLink(linkToken.value)
}

const handleTwoFactorVerifiedAndRedirect = async (tokenData) => {
  await handleTwoFactorVerified(tokenData)
  
  // Redirect based on user status
  if (tokenData.new_user) {
    router.push({ name: "forms-create" })
    useAlert().success("Pronto! Você foi cadastrado via OIDC. Bem-vindo ao OpnForm.")
  } else {
    router.push({ name: "home" })
    useAlert().success("Login realizado com sucesso!")
  }
}

onMounted(() => {
  // Set a timeout to ensure we don't get stuck in loading state
  const timeoutId = setTimeout(() => {
    if (loading.value) {
      loading.value = false
      error.value = 'A autenticação expirou. Tente novamente.'
    }
  }, 10000) // 10 second timeout
  
  handleCallback().finally(() => {
    clearTimeout(timeoutId)
  })
})
</script>
