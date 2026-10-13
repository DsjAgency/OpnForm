<template>
  <PageContainer spacing="md">
    <PageSection
      title="Modelos de PDF"
      description="Gere documentos PDF a partir das respostas do formulário."
    >
      <template #actions>
        <UDropdownMenu
          :items="createMenuItems"
          :content="{ side: 'bottom', align: 'end' }"
          arrow
        >
          <UButton
            color="primary"
            icon="i-heroicons-plus"
            trailing-icon="i-heroicons-chevron-down"
            :loading="uploading || creatingFromScratch"
          >
            Adicionar modelo
          </UButton>
        </UDropdownMenu>
        <input
          ref="fileInput"
          type="file"
          accept=".pdf"
          class="hidden"
          @change="handleFileUpload"
        >
      </template>

      <DashboardLoadingBlock :loading="isLoading">
        <template #skeleton>
          <div class="space-y-4">
            <DashboardPanel
              v-for="i in 3"
              :key="i"
              padding="sm"
            >
              <div class="animate-pulse flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <div class="h-10 w-10 bg-neutral-200 rounded" />
                  <div>
                    <div class="h-4 w-32 bg-neutral-200 rounded mb-2" />
                    <div class="h-3 w-24 bg-neutral-200 rounded" />
                  </div>
                </div>
                <div class="h-8 w-20 bg-neutral-200 rounded" />
              </div>
            </DashboardPanel>
          </div>
        </template>

        <div
          v-if="templates.length"
          class="space-y-4"
        >
          <DashboardPanel
            v-for="template in templates"
            :key="template.id"
            padding="sm"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="h-10 w-10 bg-neutral-100 rounded flex items-center justify-center">
                  <UIcon
                    name="material-symbols:picture-as-pdf-rounded"
                    class="h-5 w-5 text-primary-600"
                  />
                </div>
                <div class="min-w-0">
                  <h3 class="font-medium text-neutral-900">
                    {{ template.name }}
                  </h3>
                  <p class="text-sm text-neutral-500 truncate">
                    Formato: {{ getFilenamePatternPreview(template) }}.pdf •
                    {{ template.page_count }} página{{ template.page_count > 1 ? 's' : '' }} •
                    {{ template.zone_mappings?.length || 0 }} zone{{ template.zone_mappings?.length >= 1 ? 's' : '' }}
                  </p>
                </div>
              </div>
              <div class="relative z-20">
                <UDropdownMenu
                  :items="getTemplateMenuItems(template)"
                  :content="{ side: 'bottom', align: 'end' }"
                  arrow
                >
                  <UButton
                    color="neutral"
                    variant="ghost"
                    icon="i-heroicons-ellipsis-horizontal"
                    size="md"
                    :loading="deletingId === template.id"
                  />
                </UDropdownMenu>
              </div>
            </div>
          </DashboardPanel>
        </div>

        <DashboardEmptyState
          v-else
          icon="i-heroicons-document-arrow-down"
          title="Nenhum modelo de PDF ainda"
          description="Envie um modelo de PDF ou crie um do zero e depois associe os campos do formulário para gerar documentos personalizados a partir das respostas."
        >
          <template #action>
            <UDropdownMenu
              :items="createMenuItems"
              :content="{ side: 'bottom', align: 'center' }"
              arrow
            >
              <UButton
                color="primary"
                icon="i-heroicons-plus"
                trailing-icon="i-heroicons-chevron-down"
                :loading="uploading || creatingFromScratch"
              >
                Adicionar modelo
              </UButton>
            </UDropdownMenu>
          </template>
        </DashboardEmptyState>
      </DashboardLoadingBlock>
    </PageSection>
  </PageContainer>
</template>

<script setup>
import PageContainer from '~/components/dashboard/PageContainer.vue'
import PageSection from '~/components/dashboard/PageSection.vue'
import DashboardPanel from '~/components/dashboard/DashboardPanel.vue'
import DashboardLoadingBlock from '~/components/dashboard/states/DashboardLoadingBlock.vue'
import DashboardEmptyState from '~/components/dashboard/states/DashboardEmptyState.vue'
import { formsApi } from '~/api/forms'
import { usePdfTemplates } from '~/composables/query/forms/usePdfTemplates'

const props = defineProps({
  form: { type: Object, required: true },
})

definePageMeta({
  middleware: ['auth', 'readonly-block'],
})

useOpnSeoMeta({
  title: computed(() => props.form 
    ? `Modelos de PDF - ${props.form.title}`
    : 'Modelos de PDF'
  ),
})

const alert = useAlert()
const router = useRouter()

// Refs
const fileInput = ref(null)
const uploading = ref(false)
const creatingFromScratch = ref(false)
const deletingId = ref(null)

// Fetch templates
const { list, upload, remove } = usePdfTemplates()
const { data: templatesData, isLoading } = list(() => props.form?.id)
const uploadTemplate = upload(() => props.form?.id)
const deleteTemplate = remove(() => props.form?.id)

const templates = computed(() => templatesData.value?.data || [])

const decodeTextEntities = (value) => {
  return value
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#039;/g, "'")
}

const getFilenamePatternPreview = (template) => {
  const pattern = template.filename_pattern || template.original_filename || template.filename || 'Modelo de PDF'
  return decodeTextEntities(
    String(pattern)
      .replace(/<span[^>]*mention-field-name="([^"]*)"[^>]*>.*?<\/span>/gi, '$1')
      .replace(/<span[^>]*>(.*?)<\/span>/gi, '$1')
      .replace(/<[^>]*>/g, '')
      .replace(/\.pdf$/i, '')
      .replace(/\s+/g, ' ')
      .trim()
  ) || 'Modelo de PDF'
}

// Create menu items (Upload PDF, Create from scratch)
const createMenuItems = [
  [
    {
      label: 'Enviar PDF',
      icon: 'i-heroicons-document-arrow-up',
      onClick: () => triggerUpload()
    },
    {
      label: 'Criar do zero',
      icon: 'i-heroicons-document-plus',
      onClick: () => handleCreateFromScratch()
    }
  ]
]

// Upload handling
const triggerUpload = () => {
  fileInput.value?.click()
}

const handleFileUpload = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('file', file)

    const response = await uploadTemplate.mutateAsync(formData)
    editTemplate(response.data)
    alert.success(response.message)
  } catch (error) {
    alert.error(error?.data?.message || error?.message || 'Não foi possível enviar o modelo de PDF.')
  } finally {
    uploading.value = false
    // Reset input
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }
}

const handleCreateFromScratch = async () => {
  creatingFromScratch.value = true
  try {
    const response = await uploadTemplate.mutateAsync({})
    editTemplate(response.data)
    alert.success(response.message)
  } catch (error) {
    alert.error(error?.data?.message || error?.message || 'Não foi possível criar o modelo de PDF.')
  } finally {
    creatingFromScratch.value = false
  }
}

// Get menu items for template dropdown
const getTemplateMenuItems = (template) => {
  return [
    [
      {
        label: 'Pré-visualizar',
        icon: 'i-heroicons-eye',
        onClick: () => previewTemplate(template)
      },
      {
        label: 'Editar',
        icon: 'i-heroicons-pencil-square-20-solid',
        onClick: () => editTemplate(template)
      }
    ],
    [
      {
        label: 'Excluir modelo',
        icon: 'i-heroicons-trash',
        onClick: () => confirmDelete(template),
        class: 'text-red-800 hover:bg-red-50 hover:text-red-600 group',
        iconClass: 'text-red-900 group-hover:text-red-800'
      }
    ]
  ]
}

// Edit template
const editTemplate = (template) => {
  router.push({
    name: 'forms-slug-pdf-editor-templateId',
    params: { slug: props.form.slug, templateId: template.id }
  })
}

// Preview template (opens PDF in new tab - works even without submissions)
const previewTemplate = async (template) => {
  try {
    const response = await formsApi.pdfTemplates.getPreviewSignedUrl(props.form.id, template.id)
    window.open(response.url, '_blank')
  } catch (error) {
    alert.error(error?.data?.message || error?.message || 'Não foi possível abrir a pré-visualização do PDF.')
  }
}

// Delete template
const confirmDelete = (template) => {
  alert.confirm(
    'Tem certeza de que quer excluir este modelo de PDF? Esta ação não pode ser desfeita.',
    async () => {
      deletingId.value = template.id
      try {
        const response = await deleteTemplate.mutateAsync(template.id)
        alert.success(response.message)
      } catch (error) {
        alert.error(error?.data?.message || error?.message || 'Não foi possível excluir o modelo.')
      } finally {
        deletingId.value = null
      }
    }
  )
}

</script>
