<template>
  <UButton
    variant="outline"
    icon="i-heroicons-key-16-solid"
    :loading="form.busy"
    @click="resetPassword"
    label="Redefinir senha"
  />
</template>

<script setup>
const props = defineProps({
    user: { type: Object, required: true }
})

const form = useForm({
  user_id: props.user.id
})

const resetPassword = () => {
    return useAlert().confirm(
        "Tem certeza de que quer enviar um e-mail de redefinição de senha?",
        () => {
            form
                .patch('/moderator/send-password-reset-email')
                .then(async (data) => {
                    useAlert().success(data.message)
                })
                .catch((error) => {
                    useAlert().error(error.data?.message || 'Não foi possível enviar o e-mail de redefinição de senha')
                })
        })
}
</script>