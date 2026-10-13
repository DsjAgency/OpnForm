<template>
  <div
    v-if="variant === 'grid'"
    class="overflow-hidden rounded-2xl border border-neutral-200/80 bg-white"
  >
    <USkeleton class="h-20 w-full rounded-none" />
    <div class="space-y-3 px-4 pb-4 pt-8">
      <USkeleton class="h-5 w-3/4" />
      <USkeleton class="h-3 w-1/3" />
      <div class="flex justify-between pt-4">
        <USkeleton class="h-4 w-10" />
        <USkeleton class="h-4 w-10" />
      </div>
      <USkeleton class="h-1.5 w-full" />
    </div>
  </div>
  <div
    v-else
    class="flex items-center gap-3 rounded-xl border border-neutral-200/80 bg-white p-3 pl-4"
  >
    <USkeleton class="size-9 rounded-lg" />
    <div class="flex-grow space-y-2">
      <USkeleton class="h-4 w-2/5" />
      <USkeleton class="h-3 w-24" />
    </div>
    <USkeleton class="h-4 w-10" />
    <USkeleton class="h-4 w-10" />
    <USkeleton class="h-8 w-8" />
  </div>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: "grid",
  },
})
</script>
