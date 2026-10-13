<template>
  <UButton
    color="form"
    :label="props.label"
    class="pointer-events-none powered-by-button"
    :class="textColorClass"
    size="sm"
    tabindex="-1"
  />
</template>

<script setup>
const props = defineProps({
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
    default: "#3B82F6", // Default blue
  },
})

function getLuminanceFromHex(hex) {
  if (!hex) {
    return 0
  }
  // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
  const shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i
  hex = hex.replace(shorthandRegex, (m, r, g, b) => {
    return r + r + g + g + b + b
  })

  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
  if (!result) {
    return 0
  }

  const r = parseInt(result[1], 16)
  const g = parseInt(result[2], 16)
  const b = parseInt(result[3], 16)

  return 0.299 * r + 0.587 * g + 0.114 * b
}

const isBright = computed(() => {
  return getLuminanceFromHex(props.color) > 150
})

const textColorClass = computed(() => {
  if (isBright.value) {
    return "text-black hover:text-black"
  }
  return "text-white hover:text-white"
})
</script>

<style>
.powered-by-button,
.powered-by-button span {
  font-family: euclid-circular-b, sans-serif !important;
}
</style>
