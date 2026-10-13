<template>
  <span
    class="relative inline-flex items-center justify-center shrink-0 overflow-hidden rounded-full bg-primary-100 text-primary-700 font-semibold ring-1 ring-black/5 select-none"
    :class="size"
  >
    <img
      v-if="src && !failed"
      :src="src"
      :alt="name || 'Foto de perfil'"
      class="size-full object-cover"
      @error="failed = true"
    >
    <span
      v-else
      :class="textSize"
      v-text="initials"
    />
  </span>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  src: { type: String, default: null },
  name: { type: String, default: '' },
  // Tailwind size classes, e.g. "size-6"
  size: { type: String, default: 'size-8' },
  textSize: { type: String, default: 'text-xs' },
})

const failed = ref(false)
watch(() => props.src, () => { failed.value = false })

const initials = computed(() => {
  const parts = (props.name || '').trim().split(/\s+/).filter(Boolean)
  if (!parts.length) return '?'
  const first = parts[0].charAt(0)
  const last = parts.length > 1 ? parts[parts.length - 1].charAt(0) : ''
  return (first + last).toUpperCase()
})
</script>
