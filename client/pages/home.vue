<template>
  <div
    v-if="workspace"
    data-testid="home-page"
    class="flex flex-col h-full min-h-screen bg-[#fcf9f8]"
  >
    <div
      class="sticky top-[49px] sm:top-0 z-50 bg-white/80 backdrop-blur-md h-[49px] border-b border-neutral-200/80 p-2 sm:px-4"
    >
      <div
        class="max-w-6xl mx-auto flex items-center justify-between flex-shrink-0 gap-2 px-2 sm:px-0"
      >
        <VTransition name="fade">
          <div
            v-if="forms?.length > 0 || isFilteringForms"
            class="flex min-w-0 flex-1 sm:flex-none items-center gap-2"
          >
            <UInput
              v-model="search"
              class="min-w-0 flex-1 sm:flex-none"
              placeholder="Buscar formulários..."
              icon="i-heroicons-magnifying-glass-solid"
            />

            <USelectMenu
              v-if="allTags.length > 0"
              v-model="selectedTags"
              :items="tagOptions"
              multiple
              placeholder="Tags"
              class="hidden sm:block"
              :ui="{ content: 'min-w-fit' }"
            />

            <UButton
              v-if="isFilteringForms"
              label="Limpar"
              variant="ghost"
              color="neutral"
              @click="clearFilters"
            />
          </div>
          <div class="grow" v-else />
        </VTransition>

        <TrackClick name="home_top_bar_create_form_click">
          <UButton
            v-if="!workspace?.is_readonly"
            icon="i-heroicons-plus"
            aria-label="Criar formulário"
            @click="router.push({ name: 'forms-create' })"
          >
            <span class="hidden sm:inline">Criar formulário</span>
          </UButton>
        </TrackClick>
      </div>
    </div>

    <div class="flex-1 overflow-y-auto">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 pb-12">
        <!-- Greeting -->
        <section class="pt-8 pb-6 f4u-fade-up">
          <p class="text-sm font-medium text-primary-700 first-letter:uppercase">
            {{ todayLabel }}
          </p>
          <div class="mt-1 flex flex-wrap items-end justify-between gap-4">
            <div>
              <h1 class="!text-2xl sm:!text-3xl !font-semibold tracking-tight text-neutral-900">
                {{ greeting }}{{ firstName ? `, ${firstName}` : '' }}
              </h1>
              <p class="mt-1 text-neutral-500">
                Veja como estão os formulários de <span class="font-medium text-neutral-700">{{ workspace.name }}</span>.
              </p>
            </div>
          </div>
        </section>

        <ClientOnly>
          <!-- Stats -->
          <section
            v-if="forms?.length > 0"
            class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4"
          >
            <HomeStatCard
              v-for="(stat, index) in stats"
              :key="stat.label"
              v-bind="stat"
              class="f4u-fade-up"
              :style="{ animationDelay: `${60 + index * 60}ms` }"
            />
          </section>

          <!-- Forms header -->
          <div
            v-if="forms?.length > 0"
            class="mt-10 mb-4 flex items-center justify-between gap-3 f4u-fade-up"
            style="animation-delay: 300ms"
          >
            <div class="flex items-center gap-2">
              <h2 class="!text-lg !font-semibold text-neutral-900">
                Seus formulários
              </h2>
              <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600">
                {{ enrichedForms.length }}
              </span>
            </div>
            <div class="flex items-center rounded-lg border border-neutral-200 bg-white p-0.5">
              <UTooltip text="Grade">
                <UButton
                  size="xs"
                  :variant="viewMode === 'grid' ? 'soft' : 'ghost'"
                  :color="viewMode === 'grid' ? 'primary' : 'neutral'"
                  icon="i-heroicons-squares-2x2"
                  aria-label="Ver em grade"
                  @click="viewMode = 'grid'"
                />
              </UTooltip>
              <UTooltip text="Lista">
                <UButton
                  size="xs"
                  :variant="viewMode === 'list' ? 'soft' : 'ghost'"
                  :color="viewMode === 'list' ? 'primary' : 'neutral'"
                  icon="i-heroicons-bars-3"
                  aria-label="Ver em lista"
                  @click="viewMode = 'list'"
                />
              </UTooltip>
            </div>
          </div>

          <VTransition name="fade">
            <div
              v-if="isFetched && !isFormsLoading && forms?.length === 0"
              class="f4u-fade-up relative overflow-hidden rounded-3xl border border-primary-100 bg-gradient-to-br from-primary-50 via-white to-white px-6 py-14 text-center"
            >
              <div class="absolute -right-16 -top-16 size-56 rounded-full bg-primary-100/60 blur-3xl" />
              <img
                src="/img/forms4u-icon.png"
                alt=""
                class="relative mx-auto size-20 drop-shadow-sm f4u-float"
              >
              <h3 class="relative mt-6 text-xl font-semibold text-neutral-900">
                Crie seu primeiro formulário
              </h3>
              <p class="relative mt-2 text-neutral-500 max-w-md mx-auto">
                Monte um formulário em minutos, compartilhe o link e acompanhe as respostas por aqui.
              </p>
              <div class="relative mt-8 flex flex-wrap justify-center gap-3">
                <UButton
                  v-if="!workspace?.is_readonly"
                  size="lg"
                  icon="i-heroicons-plus"
                  label="Criar formulário"
                  @click="router.push({ name: 'forms-create' })"
                />
                <UButton
                  size="lg"
                  color="neutral"
                  variant="outline"
                  icon="i-heroicons-document-duplicate"
                  label="Ver modelos"
                  @click="router.push({ name: 'templates-my-templates' })"
                />
              </div>
            </div>

            <div
              v-if="
                isFetched &&
                !isFormsLoading &&
                forms?.length > 0 &&
                enrichedForms.length === 0
              "
              class="text-center py-16 px-4 rounded-2xl border border-dashed border-neutral-300 bg-white"
            >
              <UIcon
                name="i-heroicons-magnifying-glass"
                class="h-10 w-10 text-neutral-300 mx-auto"
              />
              <h3 class="mt-4 text-lg font-semibold text-neutral-900">
                Nenhum formulário encontrado
              </h3>
              <p class="mt-1 text-sm text-neutral-500">
                Nenhum formulário corresponde à sua busca e aos filtros.
              </p>
              <UButton
                v-if="isFilteringForms"
                class="mt-6"
                label="Limpar filtros"
                variant="soft"
                @click="clearFilters"
              />
            </div>

            <div v-if="enrichedForms.length > 0">
              <div :class="listClasses">
                <FormCard
                  v-for="(form, index) in enrichedForms"
                  :key="form.id"
                  :form="form"
                  :variant="viewMode"
                  class="f4u-fade-up"
                  :style="{ animationDelay: `${Math.min(index, 12) * 40 + 340}ms` }"
                />
              </div>

              <div v-if="isLoadingMore" :class="[listClasses, 'mt-4']">
                <FormCardSkeleton :variant="viewMode" />
                <FormCardSkeleton :variant="viewMode" class="opacity-60" />
                <FormCardSkeleton :variant="viewMode" class="opacity-30" />
              </div>

              <div
                v-else-if="!isComplete && totalPages > 1"
                class="flex justify-center items-center py-4"
              >
                <div class="text-sm text-neutral-500">
                  {{ currentPage }} de {{ totalPages }} páginas carregadas
                </div>
              </div>
            </div>

            <div v-if="isFormsLoading" :class="listClasses">
              <FormCardSkeleton :variant="viewMode" />
              <FormCardSkeleton :variant="viewMode" />
              <FormCardSkeleton :variant="viewMode" />
            </div>
          </VTransition>
          <template #fallback>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <FormCardSkeleton />
              <FormCardSkeleton />
              <FormCardSkeleton />
            </div>
          </template>
        </ClientOnly>
      </div>
    </div>
    <div id="home-portals" class="z-20" />

  </div>
</template>

<script setup>
import { useFuse } from "@vueuse/integrations/useFuse"
import FormCard from "~/components/pages/home/FormCard.vue"
import FormCardSkeleton from "~/components/pages/home/FormCardSkeleton.vue"
import TrackClick from "~/components/global/TrackClick.vue"
import HomeStatCard from "~/components/pages/home/HomeStatCard.vue"
import { useLocalStorage } from "@vueuse/core"

definePageMeta({
  middleware: ["auth"],
  layout: "dashboard",
})

useOpnSeoMeta({
  title: "Seus formulários",
  description:
    "Todos os seus formulários estão aqui. Crie novos formulários ou atualize os existentes.",
})

// Composables
const router = useRouter()
const { current: workspace, currentId: workspaceId } = useCurrentWorkspace()

const {
  forms,
  isLoading: isFormsLoading,
  isFetchingNextPage: isLoadingMore,
  isFetched,
  currentPage,
  totalPages,
  isComplete,
} = useFormsList(workspaceId, {
  fetchAll: true,
  enabled: computed(() => import.meta.client && !!workspaceId.value),
})

const { data: user } = useAuth().user()

// Greeting
const firstName = computed(() => (user.value?.name || "").trim().split(/\s+/)[0] || "")
const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return "Bom dia"
  if (hour < 18) return "Boa tarde"
  return "Boa noite"
})
const todayLabel = computed(() =>
  new Intl.DateTimeFormat("pt-BR", { weekday: "long", day: "numeric", month: "long" }).format(new Date()),
)

// Grid or list, remembered per browser
const viewMode = useLocalStorage("forms4u-home-view", "grid")
const listClasses = computed(() =>
  viewMode.value === "grid"
    ? "grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
    : "flex flex-col gap-2",
)

// Workspace totals
const stats = computed(() => {
  const list = forms.value || []
  const views = list.reduce((sum, f) => sum + (f.views_count || 0), 0)
  const submissions = list.reduce((sum, f) => sum + (f.submissions_count || 0), 0)
  const open = list.filter((f) => !f.is_closed && f.visibility === "public").length
  return [
    { label: "Formulários", value: list.length, icon: "i-heroicons-document-text", hint: `${open} ${open === 1 ? "aberto" : "abertos"}` },
    { label: "Respostas", value: submissions, icon: "i-heroicons-inbox-arrow-down", hint: "no total" },
    { label: "Visualizações", value: views, icon: "i-heroicons-eye", hint: "no total" },
    {
      label: "Taxa de resposta",
      value: views > 0 ? Math.min(100, Math.round((submissions / views) * 100)) : 0,
      suffix: "%",
      icon: "i-heroicons-arrow-trending-up",
      hint: "respostas ÷ visualizações",
    },
  ]
})

// State
const search = ref("")
const debouncedSearch = refDebounced(search, 500)
const selectedTags = ref([])

// Methods
const clearFilters = () => {
  search.value = ""
  selectedTags.value = []
}

// Computed
const isFilteringForms = computed(() => {
  return (
    (search.value !== "" && search.value !== null) ||
    selectedTags.value.length > 0
  )
})

// Extract all unique tags from forms
const allTags = computed(() => {
  if (!forms.value) return []

  const tagsSet = new Set()
  forms.value.forEach((form) => {
    if (form.tags && form.tags.length) {
      form.tags.forEach((tag) => tagsSet.add(tag))
    }
  })

  return Array.from(tagsSet).sort()
})

const tagOptions = computed(() =>
  allTags.value.map((tag) => ({ label: tag, value: tag })),
)

const baseForms = computed(() => {
  if (!forms.value) return []
  return forms.value.filter((form) => {
    if (selectedTags.value.length === 0) return true
    const selectedTagStrings = selectedTags.value
      .map((t) => (typeof t === "string" ? t : t?.value))
      .filter(Boolean)
    return form.tags && form.tags.length
      ? selectedTagStrings.every((tag) => form.tags.includes(tag))
      : false
  })
})

const { results: fuseResults } = useFuse(debouncedSearch, baseForms, {
  fuseOptions: {
    keys: ["title", "slug", "tags"],
    threshold: 0.3,
    ignoreLocation: true,
    includeScore: false,
  },
  matchAllWhenSearchEmpty: true,
})

const enrichedForms = computed(() => {
  const base = baseForms.value
  if (!base || base.length === 0) return []
  const results = fuseResults.value
  return results && results.length > 0 ? results.map((r) => r.item) : base
})
</script>
