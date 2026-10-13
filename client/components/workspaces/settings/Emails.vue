<template>
  <div class="space-y-4">
    <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h3 class="text-lg font-medium text-neutral-900">Configurações de e-mail</h3>
        <p class="mt-1 text-sm text-neutral-500">
          Configure um remetente SMTP personalizado para este espaço de trabalho.
        </p>
        <PlanTag
          :required-tier="isSelfHosted ? 'self_hosted' : 'pro'"
        />
      </div>

      <UButton
        label="Ajuda"
        icon="i-heroicons-question-mark-circle"
        variant="outline"
        color="neutral"
        @click="crisp.openHelpdeskArticle('how-to-send-emails-using-your-own-domain-name-and-email-address-13kkcif')"
      />
    </div>

    <UAlert
      v-if="isSelfHosted"
      icon="i-heroicons-information-circle"
      color="info"
      variant="subtle"
      title="O envio de e-mails da instância inteira é configurado separadamente"
      description="Use as variáveis de ambiente MAIL_* para configurar o remetente padrão de toda a instância auto-hospedada. Estas configurações substituem esse remetente neste espaço de trabalho e exigem uma licença Enterprise auto-hospedada."
      :actions="[{
        label: 'Documentação de e-mail',
        icon: 'i-heroicons-arrow-top-right-on-square',
        color: 'info',
        variant: 'solid',
        to: 'https://docs.opnform.com/configuration/email-setup',
        target: '_blank'
      }]"
    />

    <UAlert
      v-if="!isSelfHosted && !canAccessSmtp"
      icon="i-heroicons-user-group-20-solid"
      class="mb-4"
      color="warning"
      variant="subtle"
      title="Plano Pro necessário"
      description="Faça upgrade da sua conta para configurar o e-mail."
      :actions="[{
        label: 'Experimentar o plano Pro',
        color: 'warning',
        variant: 'solid',
        onClick: () => openUpgradeModal()
      }]"
    />

    <VForm size="sm">
      <form
        @submit.prevent="saveChanges"
      >
        <div class="max-w-sm">
          <TextInput
            :form="emailSettingsForm"
            name="host"
            :required="true"
            :disabled="!canAccessSmtp"
            label="Host/Servidor"
            class="mt-2"
            placeholder="smtp.example.com"
          />
          <TextInput
            :form="emailSettingsForm"
            name="port"
            :required="true"
            :disabled="!canAccessSmtp"
            label="Porta"
            placeholder="587"
          />
          <OptionSelectorInput
            :form="emailSettingsForm"
            name="encryption"
            :disabled="!canAccessSmtp"
            label="Criptografia"
            :options="encryptionOptions"
            :columns="3"
            seamless
          />
          <TextInput
            :form="emailSettingsForm"
            name="username"
            :required="true"
            :disabled="!canAccessSmtp"
            label="Usuário"
            placeholder="Usuário"
          />
          <TextInput
            :form="emailSettingsForm"
            name="password"
            native-type="password"
            :required="true"
            :disabled="!canAccessSmtp"
            label="Senha"
            placeholder="Senha"
          />
          <TextInput
            :form="emailSettingsForm"
            name="sender_address"
            :disabled="!canAccessSmtp"
            label="Endereço do remetente"
            placeholder="sender@example.com"
          />
        </div>

        <div class="mt-4 flex items-center justify-between w-full max-w-sm flex-wrap gap-2">
          <UButton
            type="submit"
            :loading="emailSettingsForm.busy"
            :disabled="!canAccessSmtp"
          >
            Save Domain(s)
          </UButton>
          <UButton
            color="neutral"
            variant="outline"
            :loading="emailSettingsForm.busy"
            :disabled="!canAccessSmtp"
            @click="clearEmailSettings"
          >
            Limpar configurações
          </UButton>
        </div>
      </form>
    </VForm>
  </div>
</template>

<script setup>
import PlanTag from "~/components/app/PlanTag.vue"

const alert = useAlert()

const { current: workspace } = useCurrentWorkspace()

const { openSubscriptionModal } = useAppModals()
const crisp = useCrisp()
const { hasFeature } = usePlanFeatures()
const canAccessSmtp = computed(() => hasFeature('custom_smtp'))
const isSelfHosted = computed(() => useFeatureFlag('self_hosted'))

const openUpgradeModal = () => {
  openSubscriptionModal({
    plan: isSelfHosted.value ? 'self_hosted' : 'pro',
    modal_title: 'Faça upgrade para enviar e-mails com seu próprio domínio'
  })
}

const encryptionOptions = [
  { name: 'tls', label: 'TLS' },
  { name: 'ssl', label: 'SSL' },
  { name: 'none', label: 'Nenhuma' }
]

const emailSettingsForm = useForm({
  host: '',
  port: '',
  encryption: 'tls',
  username: '',
  password: '',
  sender_address: ''
})

onMounted(() => {
  initEmailSettings()
})

watch(
  () => workspace,
  () => {
    initEmailSettings()
  },
)

const clearEmailSettings = () => {
  emailSettingsForm.reset()
  saveChanges()
}

const saveChanges = () => {
  if (isSelfHosted.value) {
    if (!canAccessSmtp.value) {
      openUpgradeModal()
      return
    }
  }

  // Update the workspace Email Settings
  emailSettingsForm
    .put("/open/workspaces/" + workspace.value.id + "/email-settings", {
      data: {
        host: emailSettingsForm?.host,
        port: emailSettingsForm?.port,
        encryption: emailSettingsForm?.encryption === 'none' ? null : emailSettingsForm?.encryption,
        username: emailSettingsForm?.username,
        password: emailSettingsForm?.password,
        sender_address: emailSettingsForm?.sender_address,
      },
    })
    .then((_data) => {
      // Cache is updated automatically by TanStack Query mutations
      alert.success("Configurações de e-mail salvas.")
    })
    .catch((error) => {
      alert.error("Não foi possível atualizar as configurações de e-mail: " + error.response.data.message)
    })
}

const initEmailSettings = () => {
  if (!workspace || !workspace.value.settings.email_settings) return
  const emailSettings = workspace.value?.settings?.email_settings
  emailSettingsForm.host = emailSettings?.host
  emailSettingsForm.port = emailSettings?.port
  emailSettingsForm.encryption = emailSettings?.encryption === null ? 'none' : (emailSettings?.encryption || 'tls')
  emailSettingsForm.username = emailSettings?.username
  emailSettingsForm.password = emailSettings?.password
  emailSettingsForm.sender_address = emailSettings?.sender_address
}
</script>
