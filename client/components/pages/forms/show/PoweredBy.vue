<template>
  <span
    class="powered-by-button pointer-events-none inline-flex items-center gap-1.5 rounded-full bg-white/90 py-1 pl-1 pr-3 text-xs font-medium text-neutral-600 shadow-sm ring-1 ring-black/5 backdrop-blur dark:bg-neutral-800/90 dark:text-neutral-300 dark:ring-white/10"
    tabindex="-1"
  >
    <span
      class="flex size-5 items-center justify-center rounded-full"
      :style="{ backgroundColor: color }"
    >
      <img src="/img/forms4u-icon.png" alt="" class="size-3.5 brightness-0 invert">
    </span>
    {{ label }}
  </span>
</template>

<script setup>
defineProps({
  source: {
    type: String,
    default: "form",
  },
  label: {
    type: String,
    default: "Feito com Forms4U",
  },
  color: {
    type: String,
    default: "#640e2c",
  },
})
</script>

<style>
.powered-by-button,
.powered-by-button span {
  font-family: euclid-circular-b, sans-serif !important;
}
</style>
