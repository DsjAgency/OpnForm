<template>
  <UBadge
    v-if="value === true"
    color="primary"
    variant="soft"
    class="mx-auto"
    size="sm"
  >
    Sim
  </UBadge>
  <UBadge
    v-else-if="value === false"
    color="neutral"
    variant="soft"
    class="mx-auto"
    size="sm"
  >
    Não
  </UBadge>
</template>

<script setup>
defineProps({
  value: {
    type: Boolean,
    required: true,
  },
})
</script>
