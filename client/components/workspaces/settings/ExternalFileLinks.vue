<template>
  <div class="space-y-4">
    <div>
      <h3 class="text-lg font-medium text-neutral-900">Links externos de arquivos</h3>
      <p class="mt-1 text-sm text-neutral-500">
        Defina por quanto tempo os links de arquivos enviados, compartilhados fora do espaço de trabalho, continuam disponíveis.
      </p>
    </div>

    <UAlert
      icon="i-heroicons-shield-check"
      color="warning"
      variant="subtle"
      title="Qualquer pessoa com o link pode acessar o arquivo até ele expirar"
      description="Esta regra vale para links em notificações por e-mail, exportações CSV, webhooks, integrações de chat e Google Sheets. Afeta apenas links gerados daqui em diante."
    />

    <VForm size="sm">
      <form @submit.prevent="saveChanges">
        <div class="max-w-xl">
          <OptionSelectorInput
            :form="externalFileLinkSettingsForm"
            name="expires_in_hours"
            label="Validade do link"
            :options="expirationOptions"
            :columns="5"
            seamless
          />
        </div>

        <div class="mt-4">
          <UButton
            type="submit"
            :loading="externalFileLinkSettingsForm.busy"
          >
            Salvar alterações
          </UButton>
        </div>
      </form>
    </VForm>
  </div>
</template>

<script setup>
const alert = useAlert()
const { current: workspace } = useCurrentWorkspace()
const { invalidateAll } = useWorkspaces()

const expirationOptions = [
  { name: 24, label: '24 horas' },
  { name: 72, label: '3 dias' },
  { name: 168, label: '7 dias' },
  { name: 336, label: '14 dias' },
  { name: 720, label: '30 dias' },
]

const externalFileLinkSettingsForm = useForm({
  expires_in_hours: 24,
})

function initSettings() {
  externalFileLinkSettingsForm.expires_in_hours = workspace.value?.settings?.external_file_links?.expires_in_hours || 24
}

function saveChanges() {
  externalFileLinkSettingsForm
    .put(`/open/workspaces/${workspace.value.id}/external-file-link-settings`)
    .then(() => {
      invalidateAll()
      alert.success('Configurações de links externos salvas.')
    })
    .catch((error) => {
      alert.error('Não foi possível atualizar as configurações de links externos: ' + (error.response?.data?.message || 'Erro desconhecido'))
    })
}

onMounted(() => {
  initSettings()
})

watch(workspace, () => {
  initSettings()
})
</script>
