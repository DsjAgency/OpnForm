<template>
  <div
    id="widget_login"
    class="flex flex-col gap-4"
  >
    <p class="text-sm text-neutral-500">
      <a
        href="https://telegram.org"
        target="_blank"
        class="text-primary-500 hover:underline"
      >Telegram</a> é um app de mensagens seguro que funciona em todos os dispositivos e plataformas. 
      Conecte sua conta para receber notificações na hora sempre que alguém responder a este formulário!
    </p>
    <div class="flex justify-center">
      <UButton
        :disabled="!botId"
        icon="i-mdi-telegram"
        @click.prevent="handleAuth"
      >
        Entrar com o Telegram
      </UButton>
    </div>
  </div>
</template>

<script setup>
defineProps({
  service: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['auth-data'])

const botId = computed(() => useFeatureFlag('services.telegram.bot_id'))

const loadTelegramWidget = () => {
  if (!botId.value) return

  const script = document.createElement('script')
  script.async = true
  script.src = 'https://telegram.org/js/telegram-widget.js'
  document.head.appendChild(script)
}

const handleAuth = () => {
  if (window.Telegram?.Login) {
    window.Telegram.Login.auth(
      { bot_id: botId.value, request_access: 'write' },
      (data) => {
        // Include intent for integration flow
        emit('auth-data', { ...data, intent: 'integration' })
      }
    )
  } else {
    useAlert().error('O login com Telegram não está disponível')
  }
}

onMounted(() => {
  loadTelegramWidget()
})
</script>

