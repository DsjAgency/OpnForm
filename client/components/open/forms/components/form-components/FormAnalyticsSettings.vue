<template>
  <VForm size="sm">
    <div class="space-y-4">
      <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h3 class="text-lg font-medium text-neutral-900">
            Análises
            <PlanTag
              class="ml-2"
              upgrade-modal-title="Upgrade to Unlock Analytics"
              upgrade-modal-description="Track form views and conversions with your preferred analytics platform. Integrate with Meta Pixel, Google Analytics, or Google Tag Manager."
            />
          </h3>
          <p class="mt-1 text-sm text-neutral-500">
            Adicione seu código de rastreamento para medir o desempenho do formulário.
          </p>
        </div>
        <UButton
          label="Ajuda"
          icon="i-heroicons-question-mark-circle"
          variant="outline"
          color="neutral"
          @click="crisp.openHelpdeskArticle('how-to-add-analytics-in-my-form-151nkc9')"
        />
      </div>

      <div class="flex flex-col lg:flex-row gap-8 mt-4 lg:items-start">
        <div class="flex-1 space-y-4 max-w-xs">
          <FlatSelectInput
            v-model="form.analytics.provider"
            name="provider"
            label="Provedor de análises"
            :options="providerOptions"
            placeholder="Selecione o provedor"
            :clearable="true"
          />
          <TextInput
            v-if="form.analytics.provider"
            v-model="form.analytics.tracking_id"
            name="tracking_id"
            class="mt-4"
            :label="trackingIdConfig.label"
            :placeholder="trackingIdConfig.placeholder"
            :help="trackingIdConfig.help"
          />
        </div>
      </div>
    </div>
  </VForm>
</template>

<script setup>
import PlanTag from "~/components/app/PlanTag.vue"

const crisp = useCrisp()
const workingFormStore = useWorkingFormStore()
const { content: form } = storeToRefs(workingFormStore)

const providerOptions = [
  { name: 'Meta Pixel', value: 'meta_pixel' },
  { name: 'Google Analytics', value: 'google_analytics' },
  { name: 'Google Tag Manager', value: 'gtm' },
]

onMounted(() => {
  if (!form.value?.analytics || Array.isArray(form.value?.analytics))
    form.value.analytics = {}

  form.value.analytics = {
    ...form.value.analytics,
    provider: form.value.analytics.provider === undefined ? null : form.value.analytics.provider,
    tracking_id: form.value.analytics.tracking_id === undefined ? null : form.value.analytics.tracking_id,
  }
})

const providerConfig = {
  meta_pixel: {
    label: 'Pixel ID',
    placeholder: '1234567890123456',
    help: 'Encontre o Pixel ID no Gerenciador de Eventos da Meta'
  },
  google_analytics: {
    label: 'Measurement ID',
    placeholder: 'G-XXXXXXXXXX',
    help: 'Encontre o Measurement ID no Google Analytics'
  },
  gtm: {
    label: 'Container ID',
    placeholder: 'GTM-XXXXXXX',
    help: 'Encontre o Container ID no Google Tag Manager'
  }
}

const trackingIdConfig = computed(() => {
  const provider = form.value?.analytics?.provider
  return providerConfig[provider] || { label: 'ID de rastreamento', placeholder: '', help: '' }
})

// Clear tracking_id when provider is cleared
watch(() => form.value?.analytics?.provider, (newVal) => {
  if (!newVal && form.value?.analytics) {
    form.value.analytics.tracking_id = null
  }
})
</script>

