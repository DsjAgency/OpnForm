<template>
  <!-- Phones only: the form sections as a horizontal, scrollable strip
       (on larger screens they live in the sidebar). -->
  <nav
    aria-label="Seções do formulário"
    class="sm:hidden -mx-4 mt-3 overflow-x-auto px-4 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
  >
    <ul class="flex w-max gap-1.5 pb-1">
      <li v-for="tab in tabs" :key="tab.name">
        <button
          type="button"
          class="flex items-center gap-1.5 whitespace-nowrap rounded-full border px-3.5 py-2 text-sm font-medium transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700"
          :class="route.name === tab.name
            ? 'border-primary-800 bg-primary-800 text-white shadow-sm shadow-primary-900/20'
            : 'border-neutral-200 bg-white text-neutral-600 active:bg-neutral-100'"
          :aria-current="route.name === tab.name ? 'page' : undefined"
          @click="router.push({ name: tab.name, params: { slug: form.slug } })"
        >
          <UIcon :name="tab.icon" class="size-4" />
          {{ tab.label }}
        </button>
      </li>
    </ul>
  </nav>
</template>

<script setup>
const props = defineProps({
  form: { type: Object, required: true },
})

const route = useRoute()
const router = useRouter()
const { current: workspace } = useCurrentWorkspace()

// Same sections and order as FormSidebar
const tabs = computed(() => [
  { label: 'Respostas', icon: 'i-heroicons-document-text', name: 'forms-slug-show-submissions' },
  { label: 'Compartilhar', icon: 'i-heroicons-share', name: 'forms-slug-show-share' },
  { label: 'Resumo', icon: 'i-heroicons-chart-pie', name: 'forms-slug-show-summary' },
  { label: 'Análises', icon: 'i-heroicons-chart-bar', name: 'forms-slug-show-stats' },
  ...(workspace.value?.is_readonly ? [] : [
    { label: 'Integrações', icon: 'i-heroicons-puzzle-piece', name: 'forms-slug-show-integrations' },
    { label: 'Modelos de PDF', icon: 'i-heroicons-document-arrow-down', name: 'forms-slug-show-pdf-templates' },
  ]),
].filter(() => !!props.form?.slug))
</script>
