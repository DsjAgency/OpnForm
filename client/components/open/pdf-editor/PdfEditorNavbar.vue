<template>
  <div class="w-full border-b border-neutral-200 dark:border-neutral-700 px-3 py-2 min-h-14 flex gap-x-2 items-center bg-white dark:bg-neutral-900">
    <a
      href="#"
      class="ml-2 flex text-blue font-semibold text-sm -m-1 hover:bg-blue-500/10 rounded-md p-1 group"
      @click.prevent="$emit('go-back')"
    >
      <Icon
        name="heroicons:arrow-left-20-solid"
        class="text-blue mr-1 w-6 h-6 group-hover:-translate-x-0.5 transition-all"
      />
    </a>

    <UButton
      color="neutral"
      variant="subtle"
      size="md"
      icon="i-heroicons-cog-6-tooth"
      label="Configurações"
      @click="settingsModal = true"
    />

    <div class="flex-grow flex justify-center gap-2">
      <EditableTag
        v-if="pdfTemplate"
        id="pdf-editor-title"
        v-model="pdfTemplate.name"
        element="h3"
        class="font-medium py-1 text-md w-48 text-neutral-500 truncate pdf-editor-title"
      />
    </div>

    <div
      class="flex items-center gap-x-2"
    >

      <slot name="before-save" />
      
      <TrackClick name="pdf_editor_help_button_clicked">
        <UTooltip
          text="Ajuda"
          class="items-center relative"
          :content="{ side: 'bottom' }"
          arrow
        >
          <UButton
            variant="ghost"
            color="neutral"
            icon="i-heroicons-question-mark-circle"
            @click.prevent="crisp.openHelpdesk()"
          />
        </UTooltip>
      </TrackClick>

      <div class="flex items-center gap-1">
        <UTooltip text="Diminuir zoom" :content="{ side: 'bottom' }" arrow>
          <UButton
            color="neutral"
            variant="ghost"
            icon="i-heroicons-magnifying-glass-minus"
            @click="pdfStore.zoomOut()"
          />
        </UTooltip>
        <UTooltip text="Aumentar zoom" :content="{ side: 'bottom' }" arrow>
          <UButton
            color="neutral"
            variant="ghost"
            icon="i-heroicons-magnifying-glass-plus"
            @click="pdfStore.zoomIn()"
          />
        </UTooltip>
      </div>

      <UButton
        color="neutral"
        variant="outline"
        icon="i-heroicons-eye"
        size="md"
        @click="previewPdf"
      >
        Pré-visualizar
      </UButton>

      <UTooltip arrow :content="{side: 'bottom'}">
        <template #content>
          <UKbd
            value="meta"
            size="xs"
          />
          <UKbd
            value="s"
            size="xs"
          />
        </template>
        <TrackClick
          name="save_pdf_template_click"
        >
          <UButton
            color="primary"
            size="md"
            :loading="saving"
            icon="i-ic-outline-save"
            @click="emit('save-pdf-template')"
            label="Salvar alterações"
          />
        </TrackClick>
      </UTooltip>
    </div>
  </div>

  <!-- Settings Modal -->
  <UModal
    v-model:open="settingsModal"
  >
    <template #header>
      <div class="flex items-center justify-between w-full">
        <div class="grow w-full">
          <h3 class="text-base font-semibold leading-6 text-neutral-900 dark:text-white">
            Configurações
          </h3>
          <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
            Configurações deste modelo de PDF
          </p>
        </div>
      </div>
    </template>
    <template #body>
      <div class="space-y-4">
        <!-- Filename Pattern -->
        <div>
          <div class="flex items-end gap-2">
            <MentionInput
              v-model="pdfTemplate.filename_pattern"
              class="flex-1"
              :mentions="filenameVariables"
              name="filename_pattern"
              label="Nome do arquivo"
              size="sm"
              placeholder="ex.: Nome do formulário - ID da resposta"
            />
            <span class="pb-2 text-sm font-mono text-neutral-400 dark:text-neutral-500 select-none">.pdf</span>
          </div>
          <p class="mt-1 text-xs text-neutral-400 dark:text-neutral-500">
            Use <span class="font-mono">@</span> para inserir variáveis
          </p>
        </div>

        <!-- Remove Branding -->
        <ToggleSwitchInput
          v-model="pdfTemplate.remove_branding"
          name="remove_branding"
          help="Oculta o rodapé 'PDF gerado com OpnForm'"
        >
          <template #label>
            <span class="text-sm">
              Remover marca
            </span>
            <PlanTag
              upgrade-modal-title="Upgrade to remove PDF branding"
            />
          </template>
        </ToggleSwitchInput>
      </div>
    </template>
  </UModal>
</template>

<script setup>
import { formsApi } from '~/api/forms'
import EditableTag from '~/components/app/EditableTag.vue'
import TrackClick from '~/components/global/TrackClick.vue'
import PlanTag from '~/components/app/PlanTag.vue'
import MentionInput from '~/components/forms/heavy/MentionInput.vue'

const emit = defineEmits(['go-back', 'save-pdf-template'])

const alert = useAlert()
const pdfStore = useWorkingPdfStore()
const { content: pdfTemplate, form, saving } = storeToRefs(pdfStore)

defineShortcuts({
  meta_s: {
    handler: () => emit('save-pdf-template')
  }
})

const crisp = useCrisp()

const settingsModal = ref(false)

const filenameVariables = [
  { id: 'form_name', name: 'Nome do formulário', type: 'text' },
  { id: 'submission_id', name: 'ID da resposta', type: 'text' },
  { id: 'date', name: 'Data', type: 'text' },
]

// Preview PDF
const previewPdf = async () => {
  if (pdfStore.hasUnsavedChanges) {
    alert.warning('Há alterações não salvas. Salve antes de pré-visualizar.')
    return
  }

  try {
    const response = await formsApi.pdfTemplates.getPreviewSignedUrl(form.value.id, pdfTemplate.value.id)
    window.open(response.url, '_blank')
  } catch (error) {
    alert.error(error?.data?.message || error?.message || 'Não foi possível abrir a pré-visualização do PDF.')
  }
}
</script>
