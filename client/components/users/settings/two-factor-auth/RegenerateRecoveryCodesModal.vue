<template>
  <UModal
    v-model:open="isOpen"
    :ui="{ content: 'sm:max-w-md' }"
  >
    <template #header>
      <h2 class="text-lg font-semibold">Gerar novos códigos de recuperação</h2>
    </template>

    <template #body>
      <div class="space-y-4">
        <UAlert
          color="warning"
          variant="subtle"
          description="Gerar novos códigos invalida todos os códigos atuais. Você precisará guardar os novos códigos imediatamente."
        />

        <div>
          <p class="text-sm font-medium text-neutral-900 mb-2">
            Digite o código de 6 dígitos do seu app autenticador:
          </p>
          <div class="flex justify-center mb-4">
            <UPinInput
              v-model="code"
              :length="6"
              type="number"
              otp
              size="lg"
              autofocus
              @complete="handleSubmit"
            />
          </div>
          <div class="text-center">
            <UButton
              variant="link"
              size="sm"
              @click="showRecoveryCode = !showRecoveryCode"
            >
              Usar código de recuperação
            </UButton>
          </div>
        </div>

        <VTransition name="fadeHeight">
          <div v-if="showRecoveryCode" class="mt-4">
            <TextInput
              v-model="recoveryCode"
              label="Código de recuperação"
              placeholder="Digite o código de recuperação"
              @keyup.enter="handleSubmit"
            />
          </div>
        </VTransition>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-end gap-2">
        <UButton
          color="neutral"
          variant="outline"
          @click="handleClose"
        >
          Cancelar
        </UButton>
        <UButton
          color="primary"
          :loading="loading"
          :disabled="code.length !== 6 && !recoveryCode"
          @click="handleSubmit"
        >
          Gerar novos códigos
        </UButton>
      </div>
    </template>
  </UModal>
</template>

<script setup>
const props = defineProps({
  show: { type: Boolean, required: true },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'regenerate'])

const isOpen = computed({
  get: () => props.show,
  set: (value) => {
    if (!value) emit('close')
  }
})

const code = ref([])
const recoveryCode = ref('')
const showRecoveryCode = ref(false)

const handleSubmit = () => {
  const codeValue = code.value.length === 6 ? code.value.join('') : null
  const finalCode = codeValue || recoveryCode.value
  
  if (finalCode) {
    emit('regenerate', {
      code: finalCode
    })
  }
}

const handleClose = () => {
  code.value = []
  recoveryCode.value = ''
  showRecoveryCode.value = false
  emit('close')
}

watch(() => props.show, (show) => {
  if (!show) {
    code.value = []
    recoveryCode.value = ''
    showRecoveryCode.value = false
  }
})
</script>

