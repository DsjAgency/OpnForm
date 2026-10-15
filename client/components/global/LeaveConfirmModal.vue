<template>
  <UModal
    :title="title"
    :description="description"
    :ui="{ content: 'sm:max-w-md' }"
  >
    <template #content>
      <div class="p-6 sm:p-7">
        <div class="flex items-start gap-4">
          <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary-50 ring-8 ring-primary-50/60">
            <UIcon
              name="i-heroicons-exclamation-triangle"
              class="size-6 text-primary-700"
            />
          </div>
          <div class="min-w-0 pt-0.5">
            <h2 class="text-lg font-semibold text-neutral-900">
              {{ title }}
            </h2>
            <p class="mt-1.5 text-sm leading-relaxed text-neutral-600">
              {{ description }}
            </p>
          </div>
        </div>

        <div class="mt-7 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
          <UButton
            color="neutral"
            variant="outline"
            size="lg"
            class="justify-center"
            icon="i-heroicons-arrow-left-on-rectangle"
            label="Sair sem salvar"
            @click="emit('close', true)"
          />
          <UButton
            color="primary"
            size="lg"
            class="justify-center"
            icon="i-heroicons-pencil-square"
            label="Continuar editando"
            autofocus
            @click="emit('close', false)"
          />
        </div>
      </div>
    </template>
  </UModal>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Sair sem salvar?'
  },
  description: {
    type: String,
    default: 'Você tem alterações que ainda não foram salvas. Se sair agora, elas serão perdidas.'
  }
})

const emit = defineEmits(['close'])
</script>
