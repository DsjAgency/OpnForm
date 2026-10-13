<template>
  <UModal
    v-model:open="isOpen"
    :content="{
      onPointerDownOutside: (event) => { if (event.target?.closest('.crisp-client')) {return event.preventDefault()}}
    }"
  >
    <template #header>
      <div class="flex items-center w-full gap-4 px-2">
        <h2 class="font-semibold">
          Convidar novo usuário
        </h2>
      </div>
    </template>

    <template #body>
      <UAlert
        v-if="paidPlansEnabled && !hasActiveLicense && !canInviteUsers"
        icon="i-heroicons-user-group-20-solid"
        class="mb-4"
        color="warning"
        variant="subtle"
        title="Plano Pro necessário"
        description="Faça upgrade da sua conta para convidar usuários ao espaço de trabalho."
        :actions="[{
          label: 'Upgrade para Pro',
          color: 'warning',
          variant: 'solid',
          onClick: () => openSubscriptionModal({
            modal_title: 'Faça upgrade para convidar usuários ao seu espaço de trabalho',
            modal_description: 'Faça upgrade para o plano Pro e libere a colaboração em equipe, marca personalizada, análises de formulários, domínios personalizados e muito mais!'
          })
        }]"
      />

      <VForm
        size="sm"
        class="my-2"
        @submit.prevent="addUser"
      >
        <TextInput
          :form="inviteUserForm"
          name="email"
          label="E-mail"
          :required="true"
          :disabled="!canInviteUsers"
          placeholder="Adicione um usuário pelo e-mail"
        />
        <FlatSelectInput
          :form="inviteUserForm"
          name="role"
          :options="roleOptions"
          :disabled="!canInviteUsers"
          placeholder="Selecione o papel do usuário"
          label="Papel"
          :required="true"
        />
        <div class="flex justify-center mt-4">
          <UButton
            type="submit"
            :disabled="!canInviteUsers"
            :loading="inviteUserMutation.isPending.value"
            icon="i-heroicons-envelope"
          >
            Convidar usuário
          </UButton>
        </div>
      </VForm>
    </template>
  </UModal>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
})

const { data: user } = useAuth().user()
const { addUser: addUserMutation } = useWorkspaceUsers()

// Local computed for active license check
const hasActiveLicense = computed(() => {
  return user.value !== null && user.value !== undefined && user.value.active_license !== null
})
const { openSubscriptionModal } = useAppModals()
const { handleLicenseError } = useLicenseUpgradeModal()
const { currentId: workspaceId } = useCurrentWorkspace()
const alert = useAlert()
const { hasFeature } = usePlanFeatures()
const canInviteUsers = computed(() => hasFeature('invite_user'))

const emit = defineEmits(['update:modelValue', 'user-added'])

const roleOptions = [
  {name: "Usuário", value: "user"},
  {name: "Administrador", value: "admin"},
  {name: "Somente leitura", value: "readonly"}
]

// Modal state
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Create mutation during setup
const inviteUserMutation = addUserMutation(workspaceId)

// Methods
const closeModal = () => {
  isOpen.value = false
}

const paidPlansEnabled = ref(useFeatureFlag('billing.enabled'))

const inviteUserForm = useForm({
  email: '',
  role: 'user'
})

const addUser = () => {
  if (!workspaceId.value) return

  inviteUserMutation.mutateAsync({
    email: inviteUserForm.email,
    role: inviteUserForm.role
  }).then((data) => {
    inviteUserForm.reset()
    alert.success(data.message || 'Usuário convidado')
    emit('user-added')
    closeModal()
  }).catch((error) => {
    const handled = handleLicenseError(error, {
      title: 'Licença Enterprise necessária para mais usuários',
      description: 'Sua instância auto-hospedada permite até 2 usuários sem licença Enterprise. Compre ou ative uma licença Enterprise auto-hospedada para convidar mais membros.'
    })
    if (handled) {
      closeModal()
      return
    }

    alert.error(error?.data?.message || "Ocorreu um erro ao adicionar o usuário")
  })
}
</script>
