<template>
  <AdminCard
    v-if="props.user.two_factor_enabled"
    title="Desativar autenticação em duas etapas"
    icon="i-heroicons-shield-exclamation-20-solid"
  >
    <div class="space-y-6 flex flex-col justify-between">
      <UAlert
        icon="i-heroicons-exclamation-triangle"
        color="error"
        variant="subtle"
      >
        <template #description>
          <div>
            Desativar a autenticação em duas etapas é uma ação crítica de segurança. 
            Antes de continuar, tenha um bom motivo e uma prova sólida de que a conta pertence à pessoa (como identidade verificada ou consentimento por escrito). 
            O usuário terá que reativar o 2FA no próximo login.
          </div>
        </template>
      </UAlert>

      <VForm @submit.prevent="submit">
        <TextAreaInput
          label="Motivo"
          name="reason"
          :form="form"
          :required="true"
          help="O motivo será enviado ao Slack, apenas para uso interno."
        />
        <div class="flex space-x-2 mt-4">
          <UButton
            block
            :loading="form.busy"
            type="submit"
            class="grow"
            :label="'Desativar autenticação em duas etapas'"
          />
        </div>
      </VForm>
    </div>
  </AdminCard>
</template>

<script setup>
const props = defineProps({
  user: { type: Object, required: true }
})
const emit = defineEmits(['user-updated'])

const alert = useAlert()

const form = useForm({
  user_id: props.user.id,
  reason: ''
})

async function submit() {
  try {
    let response
    response = await form.post('/moderator/disable-two-factor-authentication')
    alert.success(response.message)
    emit('user-updated', response.user)
    form.reset()
  } catch (error) {
    alert.error(error.data?.message || 'Ocorreu um erro.')
  }
}
</script> 