<template>
  <UModal
    v-model:open="isOpen"
    :ui="{ content: 'sm:max-w-md' }"
  >
    <template #header>
      <h2 class="text-lg font-semibold">Códigos de recuperação</h2>
    </template>

    <template #body>
      <div class="space-y-4">
        <!-- Recovery codes display -->
        <div v-if="codes.length > 0" class="space-y-4">
          <UAlert
            color="warning"
            variant="subtle"
            description="Estes códigos de recuperação só serão exibidos uma vez. Guarde-os em um lugar seguro. Você pode usá-los para entrar na sua conta se perder o acesso ao app autenticador."
          />

          <div class="space-y-2">
            <div
              v-for="(codeItem, index) in codes"
              :key="index"
              class="flex items-center justify-center p-2 bg-neutral-50 rounded font-mono text-sm text-center"
            >
              <span>{{ typeof codeItem === 'string' ? codeItem : codeItem.code }}</span>
              <span
                v-if="typeof codeItem === 'object' && codeItem.used_at"
                class="text-xs text-neutral-500 ml-2 font-sans"
              >
                Usado em {{ formatDate(codeItem.used_at) }}
              </span>
            </div>
          </div>

          <div class="flex gap-2">
            <UButton
              block
              color="neutral"
              variant="outline"
              @click="handleCopy"
            >
              Copiar todos os códigos
            </UButton>
            <UButton
              block
              color="primary"
              @click="handleClose"
            >
              Já guardei os códigos
            </UButton>
          </div>
        </div>
      </div>
    </template>
  </UModal>
</template>

<script setup>
const props = defineProps({
  show: { type: Boolean, required: true },
  codes: { type: Array, default: () => [] },
  justRegenerated: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'copy'])

const isOpen = computed({
  get: () => props.show,
  set: (value) => {
    if (!value) emit('close')
  }
})

const handleCopy = () => {
  emit('copy')
}

const handleClose = () => {
  emit('close')
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

</script>

