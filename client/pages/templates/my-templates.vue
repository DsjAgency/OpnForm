<template>
  <div class="flex flex-col h-full bg-white">
    <div class="sticky top-0 z-50 bg-white border-b border-neutral-200 p-2 sm:px-4">
      <div class="max-w-4xl mx-auto flex items-center justify-between flex-wrap flex-shrink-0 gap-2 px-2 sm:px-0">
          <h1 class="text-lg font-semibold text-neutral-900">Modelos</h1>
      </div>
    </div>

    <div class="flex-1 overflow-y-auto p-4">
      <div class="max-w-4xl mx-auto">
        <VTransition name="fade">

            <templates-list
              v-if="loading || templates?.length > 0"
              grid-classes="grid-cols-1 mt-8 sm:grid-cols-2 lg:grid-cols-3"
              :templates="templates"
              :loading="loading"
              :show-types="false"
              :show-industries="false"
            />

          <div v-else class="text-center py-16 px-4">
            <UIcon name="i-heroicons-document-duplicate" class="h-12 w-12 text-neutral-400 mx-auto" />
            <h3 class="mt-4 text-lg font-semibold text-neutral-900">
              Ainda não há modelos
            </h3>
            <p class="mt-1 text-sm text-neutral-500 max-w-md mx-auto">
              Os modelos criados pela equipe da Cidadania4U aparecerão aqui. Para criar um, abra um formulário e use a opção "Criar modelo" no menu de ações.
            </p>
          </div>
        </VTransition>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: "auth",
  layout: "dashboard",
})

useOpnSeoMeta({
  title: "Modelos",
  description: "Modelos de formulário da Cidadania4U.",
})

const { list } = useTemplates()

// All templates from our own instance: publicly listed ones plus the user's own
const { data: templates, isLoading: loading } = list()
</script>
