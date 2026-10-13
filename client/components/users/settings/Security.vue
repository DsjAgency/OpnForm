<template>
  <div class="space-y-8">
    <!-- Password Section -->
    <div class="space-y-4">
      <div>
        <h3 class="text-lg font-medium text-neutral-900">Alterar senha</h3>
        <p class="text-sm text-neutral-500 mt-1">
          Atualize sua senha para manter a conta segura.
        </p>
      </div>

      <VForm size="sm">
        <form
          @submit.prevent="updatePassword"
        >
          <div class="max-w-sm">
            <TextInput
              :form="passwordForm"
              name="current_password"
              label="Senha atual"
              native-type="password"
              placeholder="Digite a senha atual"
              :required="true"
            />

            <TextInput
              :form="passwordForm"
              name="password"
              label="Nova senha"
              native-type="password"
              placeholder="Digite a nova senha"
              :required="true"
              @focus="isPasswordFocused = true"
              @blur="isPasswordFocused = false"
            />
            <PasswordStrengthIndicator 
              v-show="isPasswordFocused" 
              :password="passwordForm.password" 
            />
            
            <TextInput
              :form="passwordForm"
              name="password_confirmation"
              label="Confirmar senha"
              native-type="password"
              placeholder="Confirme a nova senha"
              :required="true"
            />
          </div>

          <div class="mt-4">
            <UButton
              type="submit"
              :loading="passwordForm.busy"
              color="primary"
            >
              Atualizar senha
            </UButton>
          </div>
        </form>
      </VForm>
    </div>

    <div class="pt-8 border-t border-neutral-200">
      <UsersSettingsTwoFactorAuth />
    </div>
  </div>
</template>

<script setup>
const alert = useAlert()

// Password form
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
})

// Password field focus state
const isPasswordFocused = ref(false)

// Update password
const updatePassword = () => {
  passwordForm
    .patch('/settings/password')
    .then(() => {
      passwordForm.reset()
      alert.success('Senha atualizada.')
    })
    .catch((error) => {
      console.error(error)
    })
}
</script> 