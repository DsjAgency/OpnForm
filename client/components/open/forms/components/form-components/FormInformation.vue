<template>
  <VForm size="sm">
    <div class="space-y-4">
      <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h3 class="text-lg font-medium text-neutral-900">Geral</h3>
          <p class="mt-1 text-sm text-neutral-500">
            Informações básicas do formulário.
          </p>
        </div>
      </div>

      <text-input
        :form="form"
        name="title"
        class="mt-4 max-w-xs"
        label="Nome do formulário"
        placeholder="Meu formulário"
        :max-char-limit="255"
        :show-char-limit="true"
      />
      <select-input
        name="tags"
        label="Etiquetas"
        clearable
        :form="form"
        help="Para organizar seus formulários"
        placeholder="Selecione etiquetas"
        class="max-w-xs"
        :multiple="true"
        :allow-creation="true"
        :options="allTagsOptions"
      />
      <flat-select-input
        name="visibility"
        label="Visibilidade do formulário"
        class="max-w-xs"
        :form="form"
        placeholder="Selecione a visibilidade"
        :options="visibilityOptions"
      />
      <div
        v-if="isFormClosingOrClosed"
        class="bg-neutral-50 border rounded-lg px-4 py-2"
      >
        <rich-text-area-input
          name="closed_text"
          :allow-fullscreen="true"
          :form="form"
          label="Texto de formulário encerrado"
          help="Esta mensagem aparece quando o formulário estiver encerrado"
          :required="false"
          wrapper-class="mb-0"
        />
      </div>

      <UButton
        v-if="copyFormOptions.length > 0"
        color="neutral"
        variant="outline"
        class="mt-4"
        icon="i-heroicons-document-duplicate"
        @click.prevent="showCopyFormSettingsModal = true"
      >
        Copiar configurações de outro formulário
      </UButton>
    </div>
  </VForm>
    
  <UModal
    v-model:open="showCopyFormSettingsModal"
    @close="showCopyFormSettingsModal = false"
  >
    <template #header>
      <div class="flex items-center w-full gap-4 px-2">
        <h2 class="text-lg font-semibold">
          Importar configurações de outro formulário
        </h2>
      </div>
    </template>
    <template #body>
      <VForm size="sm">
        <select-input
          v-model="copyFormId"
          name="copy_form_id"
          label="Copiar configurações de"
          placeholder="Escolha um formulário"
          :searchable="copyFormOptions.length > 5"
          :options="copyFormOptions"
        />
        <div class="mt-4 flex items-center justify-between">
          <UButton
            @click="copySettings"
          >
            Confirmar e copiar
          </UButton>
          <UButton
            color="neutral"
            variant="outline"
            @click="showCopyFormSettingsModal = false"
          >
            Cancelar
          </UButton>
        </div>
      </VForm>
    </template>
  </UModal>
</template>

<script setup>
import clonedeep from 'clone-deep'
import { default as _has } from 'lodash/has'

const alert = useAlert()
const workingFormStore = useWorkingFormStore()
const { content: form } = storeToRefs(workingFormStore)

// Get forms list for current workspace
const { currentId: workspaceId } = useCurrentWorkspace()
const { forms } = useFormsList(workspaceId, {
  enabled: computed(() => !!workspaceId.value)
})

// Reactive state
const showCopyFormSettingsModal = ref(false)
const copyFormId = ref(null)

// Computed properties
const visibilityOptions = [
  {
    name: 'Publicado',
    value: 'public',
  },
  {
    name: 'Rascunho - não acessível publicamente',
    value: 'draft',
  },
  {
    name: 'Encerrado - não aceita novas respostas',
    value: 'closed',
  },
]

const copyFormOptions = computed(() => {
  if (!forms.value) return []
  return forms.value
    .filter((formItem) => {
      return form.value.id !== formItem.id
    })
    .map((formItem) => {
      return {
        name: formItem.title,
        value: formItem.id,
      }
    })
})

const allTagsOptions = computed(() => {
  if (!forms.value) return []
  
  // Extract all unique tags from forms
  let tags = []
  forms.value.forEach((formItem) => {
    if (formItem.tags && formItem.tags.length) {
      if (typeof formItem.tags === "string" || formItem.tags instanceof String) {
        tags = tags.concat(formItem.tags.split(","))
      } else if (Array.isArray(formItem.tags)) {
        tags = tags.concat(formItem.tags)
      }
    }
  })
  
  return [...new Set(tags)].map((tagname) => {
    return {
      name: tagname,
      value: tagname,
    }
  })
})

// New computed property for v-if condition
const isFormClosingOrClosed = computed(() => {
  return form.value.closes_at || form.value.visibility === 'closed'
})

// Methods
const copySettings = () => {
  if (copyFormId.value == null) {
    alert.error('Selecione um formulário para copiar as configurações')
    return
  }

  const copyForm = clonedeep(
    forms.value?.find(form => form.id === copyFormId.value),
  )
  if (!copyForm)
    return;

  // Clean copy from form
  [
    "title",
    "properties",
    "cleanings",
    "views_count",
    "submissions_count",
    "workspace",
    "workspace_id",
    "updated_at",
    "share_url",
    "slug",
    "notion_database_url",
    "id",
    "database_id",
    "database_fields_update",
    "creator",
    "created_at",
    "deleted_at",
    "last_edited_human",
  ].forEach((property) => {
    if (_has(copyForm, property))
      delete copyForm[property]
  })

  // Apply changes
  Object.keys(copyForm).forEach((property) => {
    form.value[property] = copyForm[property]
  })
  showCopyFormSettingsModal.value = false
  alert.success('Configurações do formulário copiadas.')
}
</script>
