<template>
  <UModal
    v-model:open="isOpen"
  >
    <template #header>
      <div class="flex items-center w-full gap-4 px-2">
        <h2 class="font-semibold">
          Criar token de acesso
        </h2>
      </div>
      <UButton
        color="neutral"
        variant="outline"
        icon="i-heroicons-question-mark-circle"
        size="sm"
        @click="crisp.openHelpdesk()"
      >
        Ajuda
      </UButton>
    </template>
 
    <template #body>
      <template v-if="token">
        <UAlert
          icon="i-heroicons-key-20-solid"
          color="success"
          variant="subtle"
          title="Copie seu token de acesso"
          description="O token só será exibido uma vez. Guarde-o em um lugar seguro."
        />
        <CopyContent
          class="mt-4"
          :content="token"
          label="Copiar token"
        />
      </template>

      <VForm v-else size="sm">
        <form
          @submit.prevent="createToken"
        >
          <div v-if="!token">
            <TextInput
              :form="tokenForm"
              name="name"
              :required="true"
              label="Nome"
            />

            <UFormField v-if="hasAdminAbilities" label="Expira em (dias)" :error="tokenForm.errors.get('expires_at')">
              <UInput v-model="expiryDays" type="number" min="1" max="90" />
            </UFormField>
            <FlatSelectInput
              :form="tokenForm"
              name="abilities"
              label="Permissões"
              :options="abilitiesOptions"
              multiple
            />
          </div>
        </form>
      </VForm>
    </template>

    <template #footer>
      <UButton
        color="neutral"
        variant="outline"
        @click="closeModal"
      >
        Fechar
      </UButton>
      <UButton
        v-if="!token"
        type="submit"
        block
        size="lg"
        :loading="tokenForm.busy"
        @click="createToken"
      >
        Criar token
      </UButton>
    </template>
  </UModal>
</template>

<script setup>
import { useQuery } from '@tanstack/vue-query'
import { tokensApi } from '~/api/tokens'
import CopyContent from "~/components/open/forms/components/CopyContent.vue"

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const { abilities, create } = useTokens()
const alert = useAlert()
const crisp = useCrisp()

const { data: adminAbilities } = useQuery({ queryKey: ['tokens', 'abilities'], queryFn: tokensApi.abilities })
const abilitiesOptions = computed(() => [...abilities, ...(adminAbilities.value ?? []).map(name => ({ name, title: name.replace('admin:', 'Admin – ').replaceAll(':', ' – ').replaceAll('-', ' ') }))].map(ability => ({
  name: ability.title,
  value: ability.name
})))

const token = ref('')
const expiryDays = ref(30)
const tokenForm = useForm({
  name: "",
  abilities: abilities.map(ability => ability.name),
  expires_at: "",
})
const hasAdminAbilities = computed(() => tokenForm.abilities.some(ability => ability.startsWith('admin:')))

// Create token mutation
const createTokenMutation = create()
let formGeneration = 0

const resetForm = () => {
  formGeneration++
  tokenForm.reset()
  expiryDays.value = 30
  token.value = ''
}

// Modal state
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => {
    if (!value) resetForm()
    emit('close', value)
  }
})
watch(() => props.modelValue, (open) => {
  if (!open) resetForm()
})

// Methods
const closeModal = () => {
  isOpen.value = false
}

function createToken() {
  const days = Number(expiryDays.value)
  if (hasAdminAbilities.value && (!Number.isInteger(days) || days < 1 || days > 90)) {
    tokenForm.errors.set('expires_at', 'Escolha uma validade entre 1 e 90 dias.')
    return
  }
  tokenForm.expires_at = hasAdminAbilities.value ? new Date(Date.now() + days * 86400000).toISOString() : null
  const generation = formGeneration
  tokenForm.mutate(createTokenMutation).then((response) => {
    if (generation === formGeneration && props.modelValue) {
      token.value = response.token || response.data?.token || response
    }
  }).catch(() => {
    alert.error("Ocorreu um erro ao criar o token")
  })
}
</script>
