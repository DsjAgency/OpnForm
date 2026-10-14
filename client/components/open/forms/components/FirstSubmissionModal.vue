<template>
  <UModal
    v-model:open="isModalOpen"
    :ui="{ content: 'sm:max-w-2xl' }"
    title="🎉 Sua primeira resposta!"
  >
    <template #body>
      <div class="text-sm text-neutral-500 mb-6">
        Parabéns! Seu formulário está no ar. Compartilhe com outras pessoas ou confira as respostas abaixo.
      </div>

      <!-- Quick Actions -->
      <div class="space-y-3 mb-6">
        <div class="flex gap-3 items-center">
          <p class="text-sm w-36 text-neutral-500 flex-shrink-0">
            Compartilhe o link do formulário:
          </p>
          <ShareFormUrl
            class="flex-grow"
            :form="form"
          />
        </div>
        <div class="flex items-center">
          <p class="text-sm w-36 text-neutral-500 flex-shrink-0">
            Confira as respostas:
          </p>
          <UButton
            color="neutral"
            variant="outline"
            icon="i-heroicons-table-cells"
            @click="trackOpenDbClick"
            label="Ver respostas"
          />
        </div>
      </div>

      <!-- Integrations Section -->
      <div class="border-t border-neutral-200 dark:border-neutral-700 pt-5">
        <div class="flex items-center justify-between mb-3">
          <p class="text-neutral-700 dark:text-neutral-200 font-semibold text-sm">
            🔗 Conecte seu formulário a outros apps
          </p>
          <NuxtLink
            :to="integrationsPageUrl"
            target="_blank"
            class="text-xs text-primary-800 hover:text-primary-900 dark:text-primary-600 hover:underline flex items-center gap-1"
            @click="trackIntegrationsLinkClick"
          >
            Ver todas as integrações
            <Icon
              name="heroicons:arrow-top-right-on-square-16-solid"
              size="12px"
            />
          </NuxtLink>
        </div>
        <p class="text-xs text-neutral-500 mb-4">
          Seja avisado na hora quando alguém responder ou sincronize os dados com suas ferramentas favoritas.
        </p>

        <!-- Featured Integration: Email -->
        <div
          role="button"
          class="bg-primary-50 dark:bg-primary-950/20 border border-primary-200 dark:border-primary-900 rounded-lg p-4 mb-4 hover:bg-primary-100 dark:hover:bg-primary-950/30 transition-colors cursor-pointer group"
          @click="openEmailIntegration"
        >
          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-10 h-10 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
              <Icon
                name="heroicons:envelope-20-solid"
                class="text-primary-800 dark:text-primary-300"
                size="20px"
              />
            </div>
            <div class="flex-grow">
              <div class="flex items-center gap-2">
                <p class="text-sm font-semibold text-neutral-800 dark:text-white">
                  Notificação por e-mail
                </p>
                <UBadge
                  variant="subtle"
                  color="success"
                  size="xs"
                >
                  Recomendado
                </UBadge>
              </div>
              <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
                Receba um e-mail sempre que alguém responder ao formulário. Ideal para acompanhar as respostas.
              </p>
            </div>
            <Icon
              name="heroicons:chevron-right"
              class="text-neutral-400 group-hover:text-primary-800 transition-colors flex-shrink-0"
              size="20px"
            />
          </div>
        </div>

        <!-- Other Popular Integrations -->
        <p class="text-xs text-neutral-500 mb-2">
          Outras integrações populares:
        </p>
        <div class="grid grid-cols-4 gap-2">
          <div
            v-for="(integration, i) in popularIntegrations"
            :key="i"
            role="button"
            class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg p-3 flex flex-col items-center justify-center hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors cursor-pointer group relative"
            @click="openIntegrationPage(integration)"
          >
            <Icon
              :name="integration.icon"
              class="w-6 h-6 text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-white transition-colors"
            />
            <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1.5 text-center font-medium truncate w-full">
              {{ integration.name }}
            </p>
            <PlanTag
              v-if="integration.required_tier && integration.required_tier !== 'free'"
              :required-tier="integration.required_tier"
              class="absolute top-1 right-1"
            />
          </div>
        </div>
      </div>
    </template>
  </UModal>
</template>

<script setup>
import ShareFormUrl from '~/components/open/forms/components/ShareFormUrl.vue'
import PlanTag from '~/components/app/PlanTag.vue'

const props = defineProps({
  show: { type: Boolean, required: true },
  form: { type: Object, required: true }
})

const emit = defineEmits(['close'])

// Modal state
const isModalOpen = computed({
  get() {
    return props.show
  },
  set(value) {
    if (!value) {
      emit("close")
    }
  }
})

const confetti = useConfetti()
const amplitude = useAmplitude()

watch(() => props.show, () => {
  if (props.show) {
    confetti.play()
    useAmplitude().logEvent('form_first_submission_modal_viewed')
  }
})

// Integrations page URL
const integrationsPageUrl = computed(() => {
  return `/forms/${props.form.slug}/show/integrations`
})

// Popular integrations to display (subset from integrations.json)
const popularIntegrations = computed(() => [
  {
    id: 'slack',
    name: 'Slack',
    icon: 'mdi:slack',
    required_tier: 'pro'
  },
  {
    id: 'google_sheets',
    name: 'Planilhas',
    icon: 'mdi:google-spreadsheet',
    required_tier: 'free'
  },
  {
    id: 'zapier',
    name: 'Zapier',
    icon: 'cib:zapier',
    required_tier: 'free'
  },
  {
    id: 'webhook',
    name: 'Webhook',
    icon: 'material-symbols:webhook',
    required_tier: 'free'
  }
])

const trackOpenDbClick = () => {
  const submissionsUrl = props.form.submissions_url
  window.open(submissionsUrl, '_blank')
  amplitude.logEvent('form_first_submission_modal_open_db_click')
}

const trackIntegrationsLinkClick = () => {
  amplitude.logEvent('form_first_submission_modal_integrations_link_click')
}

const openEmailIntegration = () => {
  amplitude.logEvent('form_first_submission_modal_email_integration_click')
  const url = `${integrationsPageUrl.value}?integration=email`
  window.open(url, '_blank')
}

const openIntegrationPage = (integration) => {
  amplitude.logEvent('form_first_submission_modal_integration_click', { integration_id: integration.id })
  const url = `${integrationsPageUrl.value}?integration=${integration.id}`
  window.open(url, '_blank')
}
</script>