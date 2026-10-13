<template>
  <div
    v-if="field.type === 'payment'"
    class="px-4"
  >
    <EditorSectionHeader
      icon="i-heroicons-credit-card-20-solid"
      title="Pagamento"
    />

    <select-input
      name="currency"
      label="Moeda"
      :options="currencyList"
      :form="field"
      :required="true"
      :searchable="true"
      :disabled="stripeAccounts.length === 0"
    />
    <MentionInput
      class="mt-4"
      name="amount"
      label="Valor"
      native-type="number"
      :form="field"
      :mentions="form.properties"
      :required="true"
      :disabled="stripeAccounts.length === 0"
    />
    <MentionInput
      class="mt-4"
      name="prefill_name"
      label="Nome no cartão"
      :form="field"
      :mentions="form.properties"
      :disabled="stripeAccounts.length === 0"
    />
    <MentionInput
      class="mt-4"
      name="prefill_email"
      label="E-mail de cobrança"
      :form="field"
      :mentions="form.properties"
      :disabled="stripeAccounts.length === 0"
    />
    
    <div v-if="stripeAccounts.length > 0">
      <select-input
        class="mt-4"
        name="stripe_account_id"
        label="Conta Stripe"
        :options="stripeAccounts"
        :form="field"
        :required="true"
      />
      <p class="mt-4 text-sm text-center text-bold">
        OU
      </p>
    </div>
    <UButton
      class="mt-4"
      icon="i-heroicons-arrow-right"
      block
      trailing
      :loading="stripeLoading"
      @click.prevent="connectStripe"
    >
      Conectar conta Stripe
    </UButton>
  </div>
</template>

<script setup>
import EditorSectionHeader from '~/components/open/forms/components/form-components/EditorSectionHeader.vue'
import stripeCurrencies from "~/data/stripe_currencies.json"
import { useWindowMessage, WindowMessageTypes } from '~/composables/useWindowMessage'

const props = defineProps({
  field: {
    type: Object,
    required: true
  },
  form: {
    type: Object,
    required: true
  }
})

const oAuth = useOAuth()
const { data: providersData, refetch} = oAuth.providers()
const stripeLoading = ref(false)

// Setup window message listener for Stripe connection
const { listen, cleanup } = useWindowMessage()

onMounted(async () => {
  await oAuth.fetchOAuthProviders()

  if(props.field?.currency === undefined || props.field?.currency === null) {
    props.field.currency = 'USD'
  }
  if(props.field?.amount === undefined || props.field?.amount === null) {
    props.field.amount = 10
  }
  
  // Auto-select first Stripe account if none is selected
  if (!props.field.stripe_account_id && stripeAccounts.value.length > 0) {
    props.field.stripe_account_id = stripeAccounts.value[0].value
  }

  // Listen for Stripe connection message
  listen(async () => {
    await refetch()
    // Auto-select first Stripe account after refresh if one isn't already selected (or maybe always select the newest? for now, first)
    if (stripeAccounts.value.length > 0) {
      props.field.stripe_account_id = stripeAccounts.value[0].value
    }
    useAlert().success('Contas Stripe atualizadas.')
  }, { 
    useMessageChannel: false, 
    acknowledge: false 
  }, WindowMessageTypes.OAUTH_PROVIDER_CONNECTED)
})

onUnmounted(() => {
  // Cleanup listener (optional, as useWindowMessage handles it)
  cleanup()
})

const stripeAccounts = computed(() => (providersData.value || []).filter((item) => item.provider === 'stripe').map((item) => ({
  name: item.name + (item.email ? ' (' + item.email + ')' : ''),
  value: item.id
})))

const currencyList = computed(() => {
  return stripeCurrencies.map((item) => ({
    name: item.name,
    value: item.code
  }))
})

const connectStripe = () => {
  stripeLoading.value = true
  oAuth.connect('stripe', false, true, true)
  setTimeout(() => {
    stripeLoading.value = false
  }, 10000)
}
</script>