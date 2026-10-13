<template>
  <VForm size="sm">
    <div class="space-y-4">
      <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h3 class="text-lg font-medium text-neutral-900">
            Código personalizado <PlanTag
              class="mb-2 block"
              upgrade-modal-title="Upgrade to Unlock Custom Code Capabilities"
              upgrade-modal-description="On the Free plan, you can explore custom code features within the form editor. Upgrade your plan to implement custom scripts, styles, and advanced tracking in live forms. Elevate your form's functionality and design with unlimited customization options."
            />
            <PlanTag
              v-if="isSelfHosted"
              required-tier="self_hosted"
              class="mb-2 block"
              upgrade-modal-title="Upgrade to Unlock Custom Code"
            />
          </h3>
          <p class="mt-1 text-sm text-neutral-500">
            O código será inserido na seção <b>head</b> da página do formulário.
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
      
      <CodeInput
        :allow-fullscreen="true"
        name="custom_code"
        class="mt-4"
        :form="form"
        :disabled="!canUseCustomCode"
        :help="customCodeHelp"
        label="Código personalizado"
        placeholder="<script>console.log('Hello World!')</script>"
      />

      <div class="pt-6">
        <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
          <div>
            <h3 class="text-lg font-medium text-neutral-900">
              CSS personalizado <PlanTag
                class="mb-2 block"
                upgrade-modal-title="Upgrade to Unlock Custom CSS"
                upgrade-modal-description="On the Free plan, you can explore custom CSS within the editor. Upgrade to apply custom styles to your live forms."
              />
              <PlanTag
                v-if="isSelfHosted"
                required-tier="self_hosted"
                class="mb-2 block"
                upgrade-modal-title="Upgrade to Unlock Custom CSS"
              />
            </h3>
            <p class="mt-1 text-sm text-neutral-500">
              O CSS será inserido no <b>head</b> da página do formulário.
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
          :form="form"
          help="Somente CSS. Exemplo: body { background: #f8fafc }"
          label="CSS personalizado"
          placeholder="body { background: #f8fafc }"
        />
      </div>
    </div>
  </VForm>
</template>

<script setup>
import PlanTag from "~/components/app/PlanTag.vue"

const workingFormStore = useWorkingFormStore()
const { content: form } = storeToRefs(workingFormStore)
const crisp = useCrisp()

const canUseCustomCode = computed(() => workingFormStore.isCustomCodeAllowed)
const isSelfHosted = computed(() => useFeatureFlag('self_hosted'))

const customCodeHelp = computed(() => {
  const hasCustomDomain = !!form.value?.custom_domain
  const allowSelfHosted = !!useFeatureFlag('custom_code.enable_self_hosted', false)
  if (canUseCustomCode.value) {
    return 'Salve as alterações e abra a página do formulário para testar.'
  }
  // In self-hosted mode with flag disabled (and no custom domain), show safety notice with docs link
  if (isSelfHosted.value && !allowSelfHosted && !hasCustomDomain) {
    return 'Por segurança, o código personalizado vem desativado na versão auto-hospedada. Ative com CUSTOM_CODE_ENABLE_SELF_HOSTED=true. Veja a documentação técnica: https://docs.opnform.com/deployment/enterprise-features/custom-code'
  }
  return 'O código personalizado exige o uso de um domínio personalizado.'
})

</script>
