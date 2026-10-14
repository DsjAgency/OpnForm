<template>
  <ErrorBoundary @on-error="onFormEditorError">
    <template #error="{ error, clearError }">
      <div class="flex-grow w-full flex items-center justify-center flex-col gap-4">
        <h1 class="text-primary-900 text-2xl font-medium">
          Ops! Algo deu errado.
        </h1>
        <p class="text-neutral-500 max-w-lg text-center">
          Parece que sua última ação causou um problema do nosso lado.
          Pedimos
          desculpas
          pelo transtorno.
        </p>
        <div class="flex gap-2 mt-4">
          <UButton
            icon="i-material-symbols-undo"
            @click="clearEditorError(error, clearError)"
          >
            Voltar um passo
          </UButton>
        </div>
      </div>
    </template>
  
    <slot />
  </ErrorBoundary>
</template>
  
<script setup>
import ErrorBoundary from '~/components/app/ErrorBoundary.vue'

const crisp = useCrisp()
const workingFormStore = useWorkingFormStore()

// Clear error and go back 1 step in history
const clearEditorError = (error, clearError) => {
  crisp.enableChatbot()
  workingFormStore.undo()
  clearError()
}
const onFormEditorError = (error) => {
  console.error('Form Editor Error Handled', error)
  crisp.pauseChatBot()
  const eventData = {
    message: error.message,
    // take first 200 characters
    stack: error.stack.substring(0, 100)
  }
  try {
    crisp.pushEvent('form-editor-error', eventData)
  } catch (e) {
    console.error('Failed to send event to crisp', e, eventData)
  }
}
</script>
  