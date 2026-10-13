<template>
  <div>
    <forgot-password-modal
      :show="showForgotModal"
      @close="showForgotModal = false"
    />

    <two-factor-verification-modal
      v-if="pendingAuthToken"
      :show="showTwoFactorModal"
      :pending-auth-token="pendingAuthToken"
      @verified="handleTwoFactorVerifiedAndRedirect"
      @cancel="handleTwoFactorCancel"
    />

    <v-form
      method="POST"
      :form="form"
      @submit.prevent="login"
    >
      <!-- Email -->
      <text-input
        name="email"
        label="E-mail"
        required
        placeholder="Seu endereço de e-mail"
        @blur="checkOidcOptions"
      />

      <UAlert
        v-if="isLinkingExistingAccount"
        class="mt-3"
        icon="i-lucide-link"
        color="primary"
        variant="subtle"
        title="Vincule sua conta existente"
        description="Entre uma vez com sua senha atual do Forms4U para vinculá-la à sua conta SSO."
      />

      <UAlert
        v-if="isOidcSignIn && isOidcRateLimited"
        class="mt-3"
        icon="i-lucide-clock-3"
        color="warning"
        variant="subtle"
        title="Aguarde antes de tentar novamente"
        :description="oidcRateLimitMessage(oidcRetryAfterSeconds)"
      />

      <!-- Password - hidden if OIDC available and not forced to show -->
      <VTransition name="fadeHeight">
      <text-input
        v-if="showPasswordField"
        ref="passwordInputRef"
        native-type="password"
        placeholder="Sua senha"
        name="password"
        label="Senha"
        required
        @input-filled="login"
      />
      </VTransition>  

      <!-- Remember Me & Forgot Password - only show when password field is visible -->
      <div v-if="showPasswordField" class="relative flex items-center mt-1.5">
        <CheckboxInput
          class="w-full md:w-1/2"
          name="remember"
          size="small"
          label="Lembrar de mim"
        />

        <div class="w-full md:w-1/2 text-right">
          <a
            href="#"
            class="text-xs hover:underline text-neutral-500 sm:text-sm hover:text-neutral-700"
            @click.prevent="showForgotModal = true"
          >
            Esqueceu sua senha?
          </a>
        </div>
      </div>

      <!-- Submit Button -->
      <UButton
        class="mt-2"
        block
        size="lg"
        :loading="form.busy"
        :disabled="form.busy || (isOidcSignIn && isOidcRateLimited)"
        type="submit"
        :label="submitButtonLabel"
      />

      <UButton
        v-if="useFeatureFlag('services.google.auth') && !useFeatureFlag('self_hosted')"
        native-type="button"
        color="neutral"
        variant="outline"
        size="lg"
        class="space-x-4 mt-4 flex items-center"
        block
        :disabled="form.busy"
        :loading="false"
        @click.prevent="signInwithGoogle"
        icon="devicon:google"
        label="Entrar com Google"
      />
      <p
        v-if="!useFeatureFlag('self_hosted')"
        class="text-neutral-500 text-sm text-center mt-4"
      >
        Não tem uma conta?
        <a
          v-if="isQuick"
          href="#"
          class="font-semibold ml-1"
          @click.prevent="$emit('openRegister')"
        >Cadastre-se</a>
        <NuxtLink
          v-else
          :to="{ name: 'register' }"
          class="font-semibold ml-1"
        >
          Cadastre-se
        </NuxtLink>
      </p>
    </v-form>

    <!-- Google One Tap -->
    <ClientOnly>
      <GoogleOneTap context="signin" />
    </ClientOnly>
  </div>
</template>

<script setup>
import ForgotPasswordModal from "../ForgotPasswordModal.vue"
import GoogleOneTap from "~/components/vendor/GoogleOneTap.vue"
import { WindowMessageTypes } from "~/composables/useWindowMessage"
import {
  getOidcRetryAfterSeconds,
  isOidcRateLimitedError,
  oidcRateLimitMessage,
} from "~/lib/oidc/rate-limit"
import { clearOidcAutomaticRetry, storeOidcStateVerifier } from "~/lib/oidc/state-verifier"

// Props
const props = defineProps({
  isQuick: {
    type: Boolean,
    required: false,
    default: false,
  },
})

// Emits
defineEmits(['openRegister'])

// Composables
const oAuth = useOAuth()
const router = useRouter()
const { login: loginMutationFactory } = useAuth()
const authFlow = useAuthFlow()
const { showTwoFactorModal, pendingAuthToken, handleTwoFactorVerified, handleTwoFactorCancel, handleTwoFactorError } = authFlow
const { linkToken, completeLinkIfNeeded } = useOidcLinking()

// Feature flags
const oidcAvailable = computed(() => useFeatureFlag('oidc.available', false))
const oidcForced = computed(() => useFeatureFlag('oidc.forced', false))

// Reactive data
const form = useForm({
  email: "",
  password: "",
  remember: false,
})

const loginMutation = loginMutationFactory()
const showForgotModal = ref(false)
const isLinkingExistingAccount = computed(() => Boolean(linkToken.value))
const showPasswordField = ref(!oidcAvailable.value || isLinkingExistingAccount.value)
const passwordInputRef = ref(null)
const oidcRetryAfterSeconds = ref(0)
let oidcRateLimitTimer = null

const isOidcSignIn = computed(() => oidcAvailable.value && !showPasswordField.value)
const isOidcRateLimited = computed(() => oidcRetryAfterSeconds.value > 0)
const submitButtonLabel = computed(() => {
  if (isOidcSignIn.value && isOidcRateLimited.value) {
    return `Tente novamente em ${oidcRetryAfterSeconds.value}s`
  }

  return isOidcSignIn.value ? 'Continuar' : 'Entrar'
})

const clearOidcRateLimitTimer = () => {
  if (oidcRateLimitTimer) {
    clearInterval(oidcRateLimitTimer)
    oidcRateLimitTimer = null
  }
}

const startOidcRateLimitTimer = (error) => {
  oidcRetryAfterSeconds.value = getOidcRetryAfterSeconds(error)
  clearOidcRateLimitTimer()

  oidcRateLimitTimer = setInterval(() => {
    oidcRetryAfterSeconds.value -= 1

    if (oidcRetryAfterSeconds.value <= 0) {
      oidcRetryAfterSeconds.value = 0
      clearOidcRateLimitTimer()
    }
  }, 1000)
}

const handleOidcRateLimitError = (error) => {
  if (!isOidcRateLimitedError(error)) return false

  startOidcRateLimitTimer(error)
  return true
}

// Watch for password field visibility to focus it
watch(showPasswordField, async (newValue) => {
  if (newValue) {
    await nextTick()
    // Find the password input element by name/id
    const passwordInput = document.querySelector('input[name="password"][type="password"]')
    if (passwordInput) {
      passwordInput.focus()
    }
  }
})

watch(linkToken, (newToken) => {
  if (newToken) {
    showPasswordField.value = true
  }
})

// Lifecycle
onMounted(() => {
  // Use the window message composable
  const windowMessage = useWindowMessage(WindowMessageTypes.LOGIN_COMPLETE)
  
  // Listen for login complete messages
  windowMessage.listen(() => {
    redirect()
  })
})

onBeforeUnmount(() => {
  clearOidcRateLimitTimer()
})

// Methods
const checkOidcOptions = async () => {
  if (isLinkingExistingAccount.value || !oidcAvailable.value || !form.email || form.busy || isOidcRateLimited.value) {
    return
  }

  form.post('/auth/oidc/options', { body: { email: form.email } })
    .then(async (response) => {
      if (response.action === 'redirect') {
        // Get the redirect URL from the backend
        try {
          clearOidcAutomaticRetry(response.slug)
          const redirectResponse = await form.post(`/auth/${response.slug}/redirect`)
          
          if (redirectResponse.redirect_url) {
            storeOidcStateVerifier(response.slug, redirectResponse.state, redirectResponse.state_verifier)
            // Redirect to OIDC provider
            window.location.href = redirectResponse.redirect_url
            return
          } else if (redirectResponse.error) {
            // Handle error from redirect endpoint
            useAlert().error(redirectResponse.error || 'Não foi possível iniciar a autenticação OIDC')
            if (!oidcForced.value) showPasswordField.value = true
            return
          }
        } catch (error) {
          if (handleOidcRateLimitError(error)) return

          // Handle network or server errors
          const errorMessage = error.response?._data?.error || error.response?._data?.message || 'Não foi possível iniciar a autenticação OIDC'
          useAlert().error(errorMessage)
          if (!oidcForced.value) showPasswordField.value = true
          return
        }
      } else if (response.action === 'blocked') {
        // OIDC is forced but no connection found
        useAlert().error('A autenticação OIDC é obrigatória. Fale com o administrador.')
        return
      } else {
        // Fallback to password login
        if (!oidcForced.value) showPasswordField.value = true
      }
    })
    .catch((error) => {
      if (handleOidcRateLimitError(error)) return

      // Form automatically handles 422 validation errors (invalid email, etc.)
      // Only show password field as fallback for non-validation errors
      if (error.response?.status !== 422 && !oidcForced.value) {
        showPasswordField.value = true
      }
    })
}

const login = () => {
  // If OIDC is available and password field is hidden, check OIDC options first
  if (oidcAvailable.value && !showPasswordField.value && form.email) {
    checkOidcOptions()
    return
  }

  // Normal password login
  if (!form.password && showPasswordField.value) {
    useAlert().error('A senha é obrigatória')
    return
  }

  if (linkToken.value) {
    form.oidc_link_token = linkToken.value
  } else {
    delete form.oidc_link_token
  }

  form.mutate(loginMutation).then(() => {
    // If 2FA modal is shown, don't redirect yet (handled in handleTwoFactorVerified)
    if (!showTwoFactorModal.value) {
      completeLinkIfNeeded().then((shouldRedirect) => {
        if (shouldRedirect) {
          redirect()
        }
      })
    }
  }).catch((error) => {
    // Check if this is a 2FA requirement (422 with requires_2fa flag)
    const twoFactorData = handleTwoFactorError(error)
    if (twoFactorData) {
      // This is a 2FA requirement, already handled in onError, just return
      return
    }
    
    // Handle other errors
    console.log(error)
    if (error.response?._data?.message == "You must change your credentials when in self host mode") {
      redirect()
    }
  })
}

const redirect = () => {
  if (props.isQuick) {
    // Use window message instead of event
    const afterLoginMessage = useWindowMessage(WindowMessageTypes.AFTER_LOGIN)
    afterLoginMessage.send(window)
    return
  }

  const intendedUrlCookie = useCookie("intended_url")

  if (intendedUrlCookie.value) {
    router.push(intendedUrlCookie.value)
    useCookie("intended_url").value = null
  } else {
    router.push({ name: "home" })
  }
}

const showOAuthError = (error) => {
  if (error.response?.status === 422 && error.response?.data?.message) {
    useAlert().error(error.response.data.message)
  } else {
    useAlert().error("Falha no login. Tente novamente.")
  }
}

const signInwithGoogle = () => {
  try {
    oAuth.guestConnect('google', true)
  } catch (error) {
    showOAuthError(error)
  }
}

const handleTwoFactorVerifiedAndRedirect = (tokenData) => {
  handleTwoFactorVerified(tokenData).then(() => {
    completeLinkIfNeeded().then((shouldRedirect) => {
      if (shouldRedirect) {
        redirect()
      }
    })
  })
}
</script>
