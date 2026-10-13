<template>
  <UModal
    v-model:open="isOpen"
    :ui="{
      content: 'w-full max-w-md'
    }"
  >
    <template #header>
      <div class="flex items-center w-full gap-4 px-2">
        <h2 class="font-semibold">
          Criar espaço de trabalho
        </h2>
      </div>
    </template>
    
    <template #body>
        <VForm size="sm">
          <form
            @submit.prevent="handleSubmit"
          >
            <text-input
              name="name"
              :form="form"
              :required="true"
              :disabled="form.busy"
              label="Nome do espaço de trabalho"
              placeholder="Meu espaço de trabalho"
            />
            <text-input
              name="emoji"
              class="mt-4"
              :form="form"
              :required="false"
              :disabled="form.busy"
              label="Emoji (opcional)"
              placeholder="🚀"
              help="Escolha um emoji para representar seu espaço de trabalho"
            />
          </form>
        </VForm>
    </template>
    <template #footer>
      <div class="flex gap-2 w-full">
        <UButton color="neutral" variant="outline" @click="closeModal">Cancelar</UButton>
        <UButton block type="submit" :loading="form.busy" @click="handleSubmit">Criar espaço de trabalho</UButton>
      </div>
    </template>
  </UModal>
</template>

<script setup>
const emit = defineEmits(['created', 'close'])

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
})

const { create } = useWorkspaces()
const appStore = useAppStore()
const alert = useAlert()

// Modal state
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('close', value)
})

// Form state
const form = useForm({
  name: '',
  emoji: ''
})

// Handle form submission
const createMutation = create()

const handleSubmit = () => {
  form.mutate(createMutation).then((response) => {
    const newWorkspace = response.workspace
    appStore.setCurrentId(newWorkspace.id)

    // Show success message
    alert.success('Agora você está no seu novo espaço de trabalho.', 10000, {
      title: 'Espaço de trabalho criado!'
    })

    // Emit created event and close modal
    emit('created', newWorkspace)
    closeModal()
  }).catch((error) => {
    console.error('Error creating workspace:', error)
    alert.error(error.data?.message || 'Algo deu errado. Tente novamente.', 10000, {
      title: 'Erro ao criar o espaço de trabalho'
    })
  })
}

// Close modal and reset form
const closeModal = () => {
  form.reset()
  isOpen.value = false
}

// Reset form when modal opens
watch(isOpen, (newValue) => {
  if (newValue) {
    form.reset()
  }
})
</script> 