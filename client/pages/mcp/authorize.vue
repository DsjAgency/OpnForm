<template>
  <div class="flex min-h-screen items-center justify-center bg-neutral-50 px-6">
    <div class="w-full max-w-md rounded-2xl border border-neutral-200 bg-white p-8 text-center shadow-sm">
      <div class="mx-auto flex w-fit items-center gap-2.5" aria-label="OpnForm">
        <img src="/img/logo.svg" alt="" class="h-10 w-10">
        <span class="text-lg font-semibold tracking-tight text-neutral-950">OpnForm</span>
      </div>
      <h1 class="mt-4 text-xl font-semibold text-neutral-950">Conectando sua conta do OpnForm</h1>
      <p class="mt-2 text-sm text-neutral-600">
        Você será redirecionado para revisar o acesso solicitado pelo seu assistente de IA.
      </p>
      <UProgress class="mt-6" animation="carousel" />
      <p v-if="error" class="mt-4 text-sm text-red-600">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
definePageMeta({ middleware: 'auth' })

const route = useRoute()
const error = ref('')

onMounted(() => {
  const requestToken = typeof route.query.request === 'string' ? route.query.request : ''

  if (!/^[A-Za-z0-9]{64}$/.test(requestToken)) {
    error.value = 'Este pedido de autorização é inválido ou expirou.'
    return
  }

  opnFetch('/mcp-oauth/session', {
    method: 'POST',
    body: { authorization_request_token: requestToken },
  })
    .then((response) => {
      window.location.assign(response.authorization_url)
    })
    .catch((requestError) => {
      error.value = requestError?.data?.message || 'Não foi possível continuar o fluxo de autorização.'
    })
})

useOpnSeoMeta({
  title: 'Conecte sua conta do OpnForm',
  robots: 'noindex, nofollow',
})
</script>
