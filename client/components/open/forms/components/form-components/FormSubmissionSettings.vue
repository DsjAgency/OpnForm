<template>
  <VForm size="sm">
    <div class="space-y-4">
      <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h3 class="text-lg font-medium text-neutral-900">Envio de respostas</h3>
          <p class="mt-1 text-sm text-neutral-500">
            Configure como as respostas do formulário são tratadas.
          </p>
        </div>
      </div>

      <div class="flex flex-wrap items-end gap-4">
        <TextInput
          name="submit_button_text"
          :form="form"
          class="max-w-xs"
          label="Texto do botão de enviar"
        />
        <TextInput
          v-if="isFocused"
          v-model="focusedNextText"
          name="focused_next_button_text"
          class="max-w-xs"
          label="Texto do botão de próximo"
          :placeholder="$t('forms.buttons.next')"
        />
      </div>
      <ToggleSwitchInput
        name="auto_save"
        :form="form"
        label="Salvar resposta automaticamente"
        help="Salva o progresso para que a pessoa possa continuar depois."
        class="mt-4"
        :disabled="hasPaymentBlock"
      />
      <UAlert
        v-if="hasPaymentBlock"
        color="primary"
        variant="subtle"
        title="Deve ficar ativado quando há um bloco de pagamento."
        class="max-w-md"
      />
      
      <FlatSelectInput
        :form="submissionOptions"
        name="databaseAction"
        class="mt-4 max-w-xs"
        label="Ação ao gravar a resposta"
        :options="[
          { name: 'Criar novo registro', value: 'create' },
          { name: 'Atualizar registro existente', value: 'update' }
        ]"
        :required="true"
      />
      <div
        v-if="submissionOptions.databaseAction == 'update'"
        class="bg-neutral-50 border rounded-lg px-4 py-2"
      >
        <div
          class="w-auto max-w-lg"
        >
          <p class="mb-2 mt-2 text-neutral-500 text-sm">
            Quando valores iguais forem encontrados nas colunas selecionadas, o (primeiro) registro existente é atualizado em vez de criar um novo. Se não houver correspondência, um novo registro é criado.
          </p>
          <select-input
            v-if="filterableFields.length"
            :form="form"
            name="database_fields_update"
            label="Campos para comparar na atualização"
            :options="filterableFields"
            multiple
            clearable
          />

          <toggle-switch-input
            v-model="clearEmptyFieldsOnUpdate"
            class="mt-4"
            label="Limpar campos vazios ao atualizar"
            help="Se ativado, campos deixados vazios apagam os valores existentes na resposta. Se desativado, só os campos preenchidos são atualizados."
          />
        </div>
      </div>

      <!-- Advanced Submission Settings -->
      <div class="mb-8">
        <h4 class="font-semibold mt-4 border-t pt-4">
          Opções avançadas de envio
        </h4>
        <p class="text-gray-500 text-sm mb-4">
          Configure opções avançadas de envio e coleta de dados.
        </p>
        
        <ToggleSwitchInput
          name="enable_partial_submissions"
          :form="form"
          help="Registra respostas incompletas para analisar onde as pessoas desistem e coletar dados parciais mesmo quando o formulário não é concluído."
        >
          <template #label>
            <span class="text-sm">
              Coletar respostas parciais
            </span>
            <PlanTag
              feature="enable_partial_submissions"
              class="ml-1"
              upgrade-modal-title="Upgrade to collect partial submissions"
              upgrade-modal-description="Capture valuable data from incomplete form submissions. Analyze where users drop off and collect partial information even when they don't complete the entire form."
            />
          </template>
        </ToggleSwitchInput>
      </div>

      <div class="mb-8 border-t pt-4">
        <h4 class="font-semibold">
          Retenção de respostas
        </h4>
        <p class="text-neutral-500 text-sm mb-4">
          Defina por quanto tempo as respostas ficam armazenadas.
        </p>

        <ToggleSwitchInput
          v-model="retentionEnabled"
          name="submission_retention_enabled"
          label="Excluir respostas antigas automaticamente"
          help="Exclui permanentemente as respostas após o período escolhido, contado a partir da última atualização."
        />

        <div
          v-if="retentionEnabled"
          class="mt-4 max-w-lg rounded-lg border border-neutral-200 bg-neutral-50 p-4"
        >
          <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <TextInput
              name="submission_retention_value"
              :form="form"
              native-type="number"
              :min="1"
              :max="3650"
              :required="true"
              label="Excluir após"
              class="w-full sm:max-w-40"
            />
            <SelectInput
              name="submission_retention_unit"
              :form="form"
              :options="SUBMISSION_RETENTION_UNITS"
              :required="true"
              class="w-full sm:max-w-48"
            />
          </div>

          <div class="mt-4 flex flex-wrap gap-2">
            <UButton
              v-for="shortcut in SUBMISSION_RETENTION_SHORTCUTS"
              :key="shortcut.label"
              :label="shortcut.label"
              color="neutral"
              variant="soft"
              size="xs"
              type="button"
              @click="applyRetentionShortcut(shortcut)"
            />
          </div>

          <UAlert
            color="warning"
            icon="i-heroicons-exclamation-triangle"
            variant="subtle"
            title="Exclusão permanente"
            description="Diminuir o período pode excluir respostas existentes na próxima limpeza (feita a cada hora). Dados e arquivos excluídos não podem ser recuperados."
            class="mt-4"
          />
        </div>
      </div>

      <ToggleSwitchInput
        class="mt-4"
        name="enable_ip_tracking"
        :form="form"
        help="Coleta e armazena o endereço IP de quem responde, para análises, prevenção de fraudes e dados geográficos. Ajuda a identificar padrões e melhorar a segurança."
      >
        <template #label>
          <span class="text-sm">
            Coletar endereços IP
          </span>
          <PlanTag
            class="ml-1"
            feature="enable_ip_tracking"
            upgrade-modal-title="Upgrade to collect IP addresses"
            upgrade-modal-description="Automatically capture submitter IP addresses to gain valuable insights into your form traffic. Analyze geographic patterns, detect suspicious activity, and enhance your form security with detailed submission analytics."
          />
        </template>
      </ToggleSwitchInput>
      <UAlert
        v-if="form.enable_ip_tracking"
        color="neutral"
        icon="i-heroicons-shield-exclamation"
        variant="subtle"
        title="LGPD e privacidade"
        description="Garanta que sua política de privacidade informe a coleta de endereços IP e obtenha o consentimento das pessoas quando necessário."
        class="mt-4 max-w-md"
      />

      <!-- Post-Submission Behavior -->
      <div class="mb-8">
        <h4 class="font-semibold mt-4 border-t pt-4">
          Após o envio <PlanTag
            upgrade-modal-title="Upgrade to customize post-submission behavior"
            upgrade-modal-description="Customize post-submission behavior: redirect users, show custom messages, or trigger actions. Upgrade to unlock advanced options for a seamless user experience. We have plenty of other pro features to enhance your form functionality and user engagement."
          />
        </h4>
        <p class="text-neutral-500 text-sm mb-4">
          Personalize o que acontece depois que a pessoa envia o formulário.
        </p>

        <OptionSelectorInput
          label="Ação após o envio"
          v-model="submissionOptions.submissionMode"
          :options="[
            { name: 'default', label: 'Mostrar página de sucesso' },
            { name: 'redirect', label: 'Redirecionar' }
          ]"
          option-key="name"
          :columns="2"
          class="mb-4 max-w-xs"
        />

        <div
          v-if="submissionOptions.submissionMode"
          class="bg-gray-50 border rounded-lg px-4 py-2"
        >
          <div class="w-auto max-w-lg">
            <template v-if="submissionOptions.submissionMode === 'redirect'">
              <MentionInput
                name="redirect_url"
                :form="form"
                :mentions="form.properties"
                :computed-variables="form.computed_variables"
                class="w-full max-w-xs"
                label="URL de redirecionamento"
                placeholder="https://www.google.com"
                :required="true"
              />
            </template>
            <template v-else>
              <rich-text-area-input
                enable-mentions
                :mentions="form.properties"
                :computed-variables="form.computed_variables"
                :allow-fullscreen="true"
                name="submitted_text"
                class="w-full"
                :form="form"
                label="Texto da página de sucesso"
                :required="false"
                :max-char-limit="10000"
                :show-char-limit="true"
              />
              <div class="flex items-center flex-wrap gap-x-4 mt-4">
                <toggle-switch-input
                  name="re_fillable"
                  class="w-full max-w-xs"
                  :form="form"
                  label="Permitir preencher de novo"
                  help="Permite preencher o formulário várias vezes"
                />
                <text-input
                  v-if="form.re_fillable"
                  name="re_fill_button_text"
                  :form="form"
                  label="Texto do botão de recomeçar"
                />
              </div>

              <!-- PDF Download on Success Page -->
              <div
                v-if="pdfTemplates.length > 0"
                class="flex items-center flex-wrap gap-x-4 mt-4"
              >
                <toggle-switch-input
                  name="pdf_download_enabled"
                  class="w-full max-w-xs"
                  :form="form"
                  label="Mostrar botão de baixar PDF"
                  help="Permite baixar um PDF na página de sucesso"
                />
                <template v-if="form.pdf_download_enabled">
                  <text-input
                    name="pdf_download_button_text"
                    :form="form"
                    label="Texto do botão de download"
                    placeholder="Baixar PDF"
                  />
                  <select-input
                    name="pdf_template_id"
                    class="w-full mt-4"
                    :form="form"
                    label="Modelo de PDF"
                    :options="pdfTemplateOptions"
                    :required="true"
                    help="Selecione o modelo de PDF usado no botão de download"
                  />
                </template>
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- Editable Submissions Settings -->
      <div class="mb-8">
        <h4 class="font-semibold mt-4 border-t pt-4">
          Respostas editáveis <PlanTag
              class="ml-1"
              upgrade-modal-title="Upgrade to use Editable Submissions"
              upgrade-modal-description="On the Free plan, you can try out all paid features only within the form editor. Upgrade your plan to allow users to update their submissions via a unique URL, and much more. Gain full access to all advanced features."
            />
        </h4>
        <p class="text-gray-500 text-sm mb-4">
          Permite editar e atualizar a resposta por meio de um link único.
        </p>
        <toggle-switch-input
          name="editable_submissions"
          class="w-full max-w-sm"
          help="Permite editar a resposta por uma URL única"
          :form="form"
          label="Ativar respostas editáveis"
        />
        <text-input
          v-if="form.editable_submissions"
          name="editable_submissions_button_text"
          class="w-full max-w-64 mt-4"
          :form="form"
          label="Texto do botão de editar resposta"
          :required="true"
        />
      </div>
    </div>
  </VForm>
</template>

<script setup>
import PlanTag from "~/components/app/PlanTag.vue"
import { usePdfTemplates } from '~/composables/query/forms/usePdfTemplates'
import {
  SUBMISSION_RETENTION_SHORTCUTS,
  SUBMISSION_RETENTION_UNITS,
  applySubmissionRetentionShortcut,
  setSubmissionRetentionEnabled,
  syncSubmissionRetentionEnabled
} from '~/lib/forms/submissionRetention.js'

const workingFormStore = useWorkingFormStore()
const { content: form } = storeToRefs(workingFormStore)

// PDF Templates for success page download
const { list } = usePdfTemplates()
const { data: templatesData } = list(() => form.value?.id)

const pdfTemplates = computed(() => templatesData.value?.data || [])

const pdfTemplateOptions = computed(() => {
  return pdfTemplates.value.map(t => ({
    name: t.name || t.original_filename,
    value: t.id
  }))
})

// Auto-select first PDF template if only one exists
watch(pdfTemplates, (templates) => {
  if (templates.length > 0 && form.value?.pdf_download_enabled && !form.value?.pdf_template_id) {
    form.value.pdf_template_id = templates[0].id
  }
}, { immediate: true })

const submissionOptions = ref({})

const filterableFields = computed(() => {
  if (submissionOptions.value.databaseAction !== "update") return []
  return form.value.properties
    .filter((field) => {
      return (
        !field.hidden &&
        !["files", "signature", "multi_select", "matrix", 'payment'].includes(field.type)
      )
    })
    .map((field) => {
      return {
        name: field.name,
        value: field.id,
      }
    })
})

const clearEmptyFieldsOnUpdate = computed({
  get: () => form.value.clear_empty_fields_on_update ?? false,
  set: (value) => { form.value.clear_empty_fields_on_update = value }
})

const isRetentionEnabled = ref(Boolean(
  form.value.submission_retention_value
  && form.value.submission_retention_unit
))

const retentionEnabled = computed({
  get: () => isRetentionEnabled.value,
  set: (enabled) => {
    isRetentionEnabled.value = enabled
    setSubmissionRetentionEnabled(form.value, enabled)
  }
})

const applyRetentionShortcut = (shortcut) => {
  applySubmissionRetentionShortcut(form.value, shortcut)
}

watch(
  [
    () => form.value.submission_retention_value,
    () => form.value.submission_retention_unit
  ],
  ([value, unit]) => {
    isRetentionEnabled.value = syncSubmissionRetentionEnabled(
      isRetentionEnabled.value,
      value,
      unit
    )
  }
)

watch(() => form.value.id, () => {
  isRetentionEnabled.value = syncSubmissionRetentionEnabled(
    false,
    form.value.submission_retention_value,
    form.value.submission_retention_unit
  )
})

watch({
  redirect_url: form.value.redirect_url,
  database_fields_update: form.value.database_fields_update
}, () => {
  if (form.value) {
    submissionOptions.value = {
      submissionMode: form.value.redirect_url ? 'redirect' : 'default',
      databaseAction: form.value.database_fields_update ? 'update' : 'create'
    }
  }
}, { immediate: true })

watch(submissionOptions, (val) => {
  if (val.submissionMode === 'default') form.value.redirect_url = null
  if (val.databaseAction === 'create') form.value.database_fields_update = null
}, { deep: true })

const hasPaymentBlock = computed(() => {
  return form.value.properties.some(property => property.type === 'payment')
})

const isFocused = computed(() => form.value?.presentation_style === 'focused')

onMounted(() => {
  // Ensure translations is a plain, writable object (avoid writing into readonly proxies)
  const t = form.value?.translations
  if (!t || typeof t !== 'object' || Array.isArray(t)) {
    form.value.translations = {}
  } else {
    form.value.translations = { ...t }
  }
})

const focusedNextText = computed({
  get() {
    return form.value?.translations?.focused_next_button_text || ''
  },
  set(val) {
    const current = form.value?.translations && typeof form.value.translations === 'object' ? form.value.translations : {}
    // Replace the entire translations object to avoid setting into a readonly proxy
    form.value.translations = { ...current, focused_next_button_text: val }
  }
})
</script>
