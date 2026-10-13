<template>
  <div class="group relative overflow-hidden rounded-2xl border border-neutral-200/80 bg-white p-4 sm:p-5 shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-primary-200">
    <div class="absolute -right-10 -bottom-10 size-24 rounded-full bg-primary-50/80 transition-transform duration-300 group-hover:scale-125" />
    <div class="relative flex items-start justify-between gap-2">
      <p class="text-sm font-medium text-neutral-500">
        {{ label }}
      </p>
      <span class="flex size-8 items-center justify-center rounded-lg bg-primary-100/70 text-primary-800">
        <UIcon :name="icon" class="size-4" />
      </span>
    </div>
    <p class="relative mt-2 text-2xl sm:text-3xl font-semibold tracking-tight text-neutral-900 tabular-nums">
      {{ displayValue }}<span v-if="suffix" class="text-lg text-neutral-500 ml-0.5">{{ suffix }}</span>
    </p>
    <p v-if="hint" class="relative mt-1 text-xs text-neutral-400">
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
import { TransitionPresets, useTransition } from "@vueuse/core"

const props = defineProps({
  label: { type: String, required: true },
  value: { type: Number, default: 0 },
  icon: { type: String, default: "i-heroicons-chart-bar" },
  suffix: { type: String, default: "" },
  hint: { type: String, default: "" },
})

// Count up from zero when the card appears or the value changes
const source = ref(0)
const animated = useTransition(source, {
  duration: 900,
  transition: TransitionPresets.easeOutCubic,
})
onMounted(() => {
  source.value = props.value
})
watch(() => props.value, (value) => {
  source.value = value
})

const displayValue = computed(() => new Intl.NumberFormat("pt-BR").format(Math.round(animated.value)))
</script>
