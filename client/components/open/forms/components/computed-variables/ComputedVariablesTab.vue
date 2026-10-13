<template>
  <div
    data-testid="computed-variables-tab"
    class="p-4"
  >
    <div class="flex justify-between items-start gap-4 mb-4">
      <div class="min-w-0">
        <h3 class="text-lg font-semibold text-gray-900">
          Variáveis
        </h3>
        <p class="text-sm text-gray-500 mt-1">
          Crie valores calculados a partir dos campos do formulário. Use-os em e-mails, mensagens de agradecimento e integrações.
        </p>
      </div>
      <UButton
        icon="i-heroicons-plus"
        color="primary"
        class="flex-shrink-0 whitespace-nowrap"
        @click="openCreateModal"
      >
        Adicionar variável
      </UButton>
    </div>

    <!-- Empty State -->
    <div
      v-if="!computedVariables || computedVariables.length === 0"
      class="text-center py-12 border-2 border-dashed border-gray-200 rounded-lg"
    >
      <Icon
        name="i-heroicons-variable"
        class="h-12 w-12 mx-auto text-gray-400"
      />
      <h3 class="mt-4 text-sm font-semibold text-gray-900">
        Nenhuma variável ainda
      </h3>
      <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">
        Variáveis calculam valores a partir das respostas. Use-as em e-mails, mensagens de agradecimento e integrações.
      </p>
      <div class="mt-4 text-sm text-gray-500">
        <p class="font-medium mb-2">Exemplos:</p>
        <ul class="space-y-1">
          <li>• Calcular o total de um pedido</li>
          <li>• Criar saudações personalizadas</li>
          <li>• Classificar respostas com condições SE</li>
        </ul>
      </div>
      <UButton
        class="mt-6"
        icon="i-heroicons-plus"
        color="primary"
        @click="openCreateModal"
      >
        Criar variável
      </UButton>
    </div>

    <!-- Variables List -->
    <div
      v-else
      class="space-y-3"
    >
      <ComputedVariableCard
        v-for="variable in computedVariables"
        :key="variable.id"
        :variable="variable"
        :form="form"
        @edit="openEditModal(variable)"
        @delete="deleteVariable(variable)"
      />
    </div>

    <!-- Info Footer -->
    <div
      v-if="computedVariables && computedVariables.length > 0"
      class="mt-6 p-3 bg-blue-50 rounded-lg"
    >
      <div class="flex items-start gap-2">
        <Icon
          name="i-heroicons-information-circle"
          class="h-5 w-5 text-blue-500 shrink-0 mt-0.5"
        />
        <p class="text-sm text-blue-700">
          Insira variáveis onde for possível usar menções com @: e-mails, mensagens de agradecimento, URLs de redirecionamento e integrações.
        </p>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <ComputedVariableModal
      v-model="showModal"
      :variable="editingVariable"
      :form="form"
      @save="saveVariable"
    />
  </div>
</template>

<script setup>
import ComputedVariableCard from './ComputedVariableCard.vue'
import ComputedVariableModal from './ComputedVariableModal.vue'
import { generateUUID } from '~/lib/utils.js'

const props = defineProps({
  editRequest: {
    type: Object,
    default: null,
  },
})

const workingFormStore = useWorkingFormStore()
const form = computed(() => workingFormStore.content)

const computedVariables = computed({
  get: () => form.value?.computed_variables || [],
  set: (value) => {
    if (form.value) {
      form.value.computed_variables = value
    }
  }
})

const showModal = ref(false)
const editingVariable = ref(null)

function openCreateModal() {
  editingVariable.value = null
  showModal.value = true
}

function openEditModal(variable) {
  editingVariable.value = { ...variable }
  showModal.value = true
}

watch(() => props.editRequest, (request) => {
  if (!request) {
    return
  }

  if (request.create) {
    openCreateModal()
    return
  }

  const indexedVariable = Number.isInteger(request.variableIndex)
    ? computedVariables.value[request.variableIndex]
    : null
  const variable = indexedVariable
    || computedVariables.value.find(item => item?.id === request.variableId)

  if (variable) {
    openEditModal(variable)
  }
}, { immediate: true })

function saveVariable(variable) {
  const variables = [...computedVariables.value]
  
  if (editingVariable.value) {
    // Update existing
    const index = variables.findIndex(v => v.id === variable.id)
    if (index !== -1) {
      variables[index] = variable
    }
  } else {
    // Create new
    variable.id = `cv_${generateUUID().replace(/-/g, '').substring(0, 12)}`
    variables.push(variable)
  }
  
  computedVariables.value = variables
  showModal.value = false
  editingVariable.value = null
}

function deleteVariable(variable) {
  computedVariables.value = computedVariables.value.filter(v => v.id !== variable.id)
}
</script>
