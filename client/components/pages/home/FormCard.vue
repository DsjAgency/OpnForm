<template>
  <!-- Grid card -->
  <div
    v-if="variant === 'grid'"
    class="group relative flex flex-col overflow-hidden rounded-2xl isolate border border-neutral-200/80 bg-white shadow-xs transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-primary-900/5 hover:border-primary-200 cursor-pointer"
  >
    <!-- Colored header -->
    <div
      class="relative h-20"
      :style="{ background: accent.background }"
    >
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -right-4 -top-8 size-24 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-150" />
        <div class="absolute right-10 -bottom-10 size-20 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-125" />
      </div>
      <div class="absolute left-4 bottom-0 translate-y-1/2 flex size-11 items-center justify-center rounded-xl bg-white shadow-md ring-1 ring-black/5 transition-transform duration-200 group-hover:scale-105">
        <UIcon name="i-heroicons-clipboard-document-list" class="size-5" :style="{ color: accent.solid }" />
      </div>
      <div class="absolute right-2 top-2 z-20">
        <ExtraMenu :form="form" :is-main-page="true" portal="#home-portals">
          <template #default="{ loading }">
            <UButton
              class="bg-white/20 text-white hover:bg-white/35 backdrop-blur-sm"
              color="neutral"
              variant="ghost"
              icon="i-heroicons-ellipsis-horizontal"
              size="sm"
              aria-label="Ações do formulário"
              :loading="loading"
            />
          </template>
        </ExtraMenu>
      </div>
    </div>

    <div class="flex flex-1 flex-col px-4 pb-4 pt-8">
      <h3 class="line-clamp-2 text-base font-semibold leading-snug text-neutral-900 group-hover:text-primary-900 transition-colors">
        {{ form.title }}
      </h3>
      <p class="mt-1 text-xs text-neutral-400" :title="form.updated_at">
        Atualizado {{ updatedLabel }}
      </p>
      <FormStatusBadges class="mt-3" :form="form" :with-tags="false" size="sm" />

      <div class="mt-auto pt-4">
        <div class="flex items-center justify-between text-sm text-neutral-500">
          <UTooltip :text="`${formatNumberWithCommas(form.views_count)} visualizações`">
            <span class="flex items-center gap-1.5">
              <UIcon name="i-heroicons-eye" class="size-4 text-neutral-400" />
              {{ formatNumber(form.views_count) }}
            </span>
          </UTooltip>
          <UTooltip :text="`${formatNumberWithCommas(form.submissions_count)} respostas`">
            <span class="flex items-center gap-1.5 font-medium text-neutral-700">
              <UIcon name="i-heroicons-inbox-arrow-down" class="size-4 text-primary-700" />
              {{ formatNumber(form.submissions_count) }}
            </span>
          </UTooltip>
        </div>
        <UTooltip :text="`Taxa de resposta: ${responseRate}%`" class="block">
          <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-neutral-100">
            <div
              class="h-full rounded-full transition-all duration-700"
              :style="{ width: `${responseRate}%`, background: accent.solid }"
            />
          </div>
        </UTooltip>
      </div>
    </div>

    <NuxtLink
      :to="{ name: 'forms-slug-show-submissions', params: { slug: form.slug } }"
      class="absolute inset-0 z-10"
      :aria-label="`Abrir ${form.title}`"
    />
  </div>

  <!-- List row -->
  <div
    v-else
    class="group relative flex items-center gap-3 rounded-xl border border-neutral-200/80 bg-white p-3 pl-4 shadow-xs transition-all duration-200 hover:border-primary-200 hover:shadow-md cursor-pointer"
  >
    <span
      class="flex size-9 shrink-0 items-center justify-center rounded-lg text-white"
      :style="{ background: accent.background }"
    >
      <UIcon name="i-heroicons-clipboard-document-list" class="size-4" />
    </span>
    <div class="min-w-0 flex-grow">
      <p class="truncate font-semibold text-neutral-900 group-hover:text-primary-900 transition-colors">
        {{ form.title }}
      </p>
      <p class="text-xs text-neutral-400">
        Atualizado {{ updatedLabel }}
      </p>
    </div>

    <div class="flex items-center gap-4 text-sm text-neutral-500">
      <FormStatusBadges class="hidden md:flex" :form="form" :with-tags="false" size="sm" />
      <UTooltip :text="`${formatNumberWithCommas(form.views_count)} visualizações`">
        <span class="flex items-center gap-1">
          <UIcon name="i-heroicons-eye" class="text-neutral-400" />
          {{ formatNumber(form.views_count) }}
        </span>
      </UTooltip>
      <UTooltip :text="`${formatNumberWithCommas(form.submissions_count)} respostas`">
        <span class="flex items-center gap-1 font-medium text-neutral-700">
          <UIcon name="i-heroicons-inbox-arrow-down" class="text-primary-700" />
          {{ formatNumber(form.submissions_count) }}
        </span>
      </UTooltip>
      <div class="relative z-20">
        <ExtraMenu :form="form" :is-main-page="true" portal="#home-portals">
          <template #default="{ loading }">
            <UButton
              color="neutral"
              variant="ghost"
              icon="i-heroicons-ellipsis-horizontal"
              aria-label="Ações do formulário"
              :loading="loading"
            />
          </template>
        </ExtraMenu>
      </div>
    </div>

    <NuxtLink
      :to="{ name: 'forms-slug-show-submissions', params: { slug: form.slug } }"
      class="absolute inset-0 z-10"
      :aria-label="`Abrir ${form.title}`"
    />
  </div>
</template>

<script setup>
import ExtraMenu from "~/components/pages/forms/show/ExtraMenu.vue"
import FormStatusBadges from "~/components/open/forms/components/FormStatusBadges.vue"
import { formatNumber, formatNumberWithCommas, timeAgo } from "~/lib/utils.js"

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  variant: {
    type: String,
    default: "grid",
  },
})

// Brand-based accents, picked by form id so each card keeps its color
const ACCENTS = [
  { solid: "#640e2c", background: "linear-gradient(135deg, #8c2c48 0%, #640e2c 60%, #4a031d 100%)" },
  { solid: "#ab4660", background: "linear-gradient(135deg, #c4637a 0%, #ab4660 55%, #8c2c48 100%)" },
  { solid: "#b4693f", background: "linear-gradient(135deg, #e3a77f 0%, #c97f55 55%, #9c5530 100%)" },
  { solid: "#4a031d", background: "linear-gradient(135deg, #640e2c 0%, #4a031d 60%, #2d000e 100%)" },
  { solid: "#8c2c48", background: "linear-gradient(135deg, #d78697 0%, #ab4660 50%, #640e2c 100%)" },
]
const accent = computed(() => ACCENTS[(props.form.id || 0) % ACCENTS.length])

const updatedLabel = computed(() => timeAgo(props.form.updated_at) || props.form.last_edited_human)

const responseRate = computed(() => {
  const views = props.form.views_count || 0
  if (!views) return 0
  return Math.min(100, Math.round(((props.form.submissions_count || 0) / views) * 100))
})
</script>
