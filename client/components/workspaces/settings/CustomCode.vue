<template>
  <div class="space-y-4">
    <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
      <div class="flex-1">
        <h3 class="text-lg font-medium text-neutral-900">
          Código personalizado
        </h3>
        <p class="mt-1 text-sm text-neutral-500">
          O código será inserido na seção <b>head</b> de todos os formulários deste espaço de trabalho. O código do espaço é aplicado primeiro e depois o código específico de cada formulário (se houver).
        </p>
      </div>
      <div class="flex gap-2">
        <UButton
          label="Documentação do SDK"
          icon="i-heroicons-code-bracket"
          variant="outline"
          color="neutral"
          to="https://docs.opnform.com/embedding/javascript-sdk#custom-code-integration"
          target="_blank"
        />
        <UButton
          label="Ajuda"
          icon="i-heroicons-question-mark-circle"
          variant="outline"
          color="neutral"
          @click="crisp.openHelpdeskArticle('how-do-i-add-custom-code-to-my-form-1amadj3')"
        />
      </div>
    </div>

    <UAlert
      v-if="!canAccessCustomCode"
      icon="i-heroicons-lock-closed"
      class="mb-4"
      color="warning"
      variant="subtle"
      :title="upgradeCalloutTitle"
      :description="upgradeCalloutDescription"
      :actions="[{
        label: upgradeCalloutActionLabel,
        color: 'warning',
        variant: 'solid',
        onClick: () => openUpgradeModal()
      }]"
    />

    <VForm size="sm">
      <form
        @submit.prevent="saveChanges"
      >
        <div class="space-y-4">
          <div>
            <CodeInput
              :allow-fullscreen="true"
              name="custom_code"
              class="mt-4"
              :form="customCodeForm"
              :disabled="!canUseCustomCode"
              :help="customCodeHelp"
              label="Código personalizado"
              placeholder="<script>console.log('Hello World!')</script>"
            />
          </div>

          <div class="pt-6">
            <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
              <div>
                <h3 class="text-lg font-medium text-neutral-900">
                  CSS personalizado
                </h3>
                <p class="mt-1 text-sm text-neutral-500">
                  O CSS será inserido no <b>head</b> de todos os formulários deste espaço de trabalho.
                </p>
              </div>
              <UButton
                label="Ajuda"
                icon="i-heroicons-question-mark-circle"
                variant="outline"
                color="neutral"
                @click="crisp.openHelpdeskArticle('can-i-style-my-form-with-some-custom-css-code-1v3dlr9')"
              />
            </div>
            <CodeInput
              :allow-fullscreen="true"
              language-mode="css"
              name="custom_css"
              class="mt-4"
              :form="customCodeForm"
              :disabled="!canAccessCustomCode"
              help="Somente CSS. Exemplo: body { background: #f8fafc }"
              label="CSS personalizado"
              placeholder="body { background: #f8fafc }"
            />
          </div>
        </div>

        <div class="mt-4">
          <UButton
            type="submit"
            :loading="customCodeForm.busy"
            :disabled="!canAccessCustomCode"
            color="primary"
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
const crisp = useCrisp()
const { current: workspace } = useCurrentWorkspace()
const { invalidateAll } = useWorkspaces()
const { hasFeature } = usePlanFeatures()
const canAccessCustomCode = computed(() => hasFeature('custom_code'))
const isSelfHosted = computed(() => useFeatureFlag('self_hosted'))
const { openSubscriptionModal } = useAppModals()

const upgradeCalloutTitle = computed(() => {
  return isSelfHosted.value ? 'Licença Enterprise necessária' : 'Plano Business necessário'
})

const upgradeCalloutDescription = computed(() => {
  if (isSelfHosted.value) {
    return 'Código e CSS personalizados no nível do espaço de trabalho exigem uma licença Enterprise auto-hospedada. Compre ou ative uma licença para inserir scripts e estilos em todos os formulários deste espaço.'
  }

  return 'Código e CSS personalizados no nível do espaço de trabalho exigem o plano Business. Faça upgrade para inserir scripts, estilos, códigos de análise e rastreamento avançado em todos os formulários deste espaço.'
})

const upgradeCalloutActionLabel = computed(() => {
  return isSelfHosted.value ? 'Comprar licença' : 'Upgrade para Business'
})

const openUpgradeModal = () => {
  openSubscriptionModal({
    plan: isSelfHosted.value ? 'self_hosted' : 'business',
    modal_title: isSelfHosted.value ? 'Licença Enterprise necessária para código personalizado' : 'Faça upgrade para usar código personalizado no espaço de trabalho',
    modal_description: upgradeCalloutDescription.value
  })
}

const customCodeForm = useForm({
  custom_code: '',
  custom_css: ''
})

const hasCustomDomain = computed(() => {
  return workspace.value?.custom_domains && workspace.value.custom_domains.length > 0
})

const allowSelfHosted = computed(() => !!useFeatureFlag('custom_code.enable_self_hosted', false))

const canUseCustomCode = computed(() => {
  if (!canAccessCustomCode.value) return false
  return hasCustomDomain.value || (isSelfHosted.value && allowSelfHosted.value)
})

const customCodeHelp = computed(() => {
  if (canUseCustomCode.value) {
    return 'Salve as alterações e abra qualquer formulário para testar. O código do espaço de trabalho é aplicado a todos os formulários deste espaço.'
  }
  if (isSelfHosted.value && !allowSelfHosted.value && !hasCustomDomain.value) {
    return 'Por segurança, o código personalizado vem desativado na versão auto-hospedada. Ative com CUSTOM_CODE_ENABLE_SELF_HOSTED=true. Veja a documentação técnica: https://docs.opnform.com/deployment/enterprise-features/custom-code'
  }
  return 'O código personalizado exige o plano Pro e um domínio personalizado configurado neste espaço de trabalho.'
})

const saveChanges = () => {
  if (!canAccessCustomCode.value) return

  customCodeForm
    .put(`/open/workspaces/${workspace.value.id}/custom-code-settings`, {
      data: {
        custom_code: customCodeForm.custom_code || null,
        custom_css: customCodeForm.custom_css || null,
      },
    })
    .then((_data) => {
      alert.success("Configurações de código personalizado salvas.")
      // Invalidate workspace cache to refresh data
      invalidateAll()
    })
    .catch((error) => {
      alert.error("Não foi possível atualizar o código personalizado: " + (error?.data?.message || error.message))
    })
}

const initCustomCode = () => {
  if (!workspace.value) return
  const settings = workspace.value.settings || {}
  customCodeForm.custom_code = settings.custom_code || ''
  customCodeForm.custom_css = settings.custom_css || ''
}

onMounted(() => {
  initCustomCode()
})

watch(
  () => workspace.value,
  () => {
    initCustomCode()
  },
  { deep: true }
)
</script>
