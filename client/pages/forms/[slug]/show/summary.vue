<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <FormSummary v-if="canAccessSummary" :form="form" />

    <div v-else class="border border-neutral-300 rounded-lg shadow-xs p-4 relative overflow-hidden max-w-5xl mx-auto space-y-6">
      <div class="absolute inset-0 z-10">
        <div class="p-5 max-w-md mx-auto flex flex-col items-center justify-center h-full">
          <p class="text-center">
            Você precisa de uma assinatura <PlanTag
              upgrade-modal-title="Upgrade today to access form summaries"
              class="mx-1"
            /> para acessar os resumos de formulários. 
          </p>
          <UButton
            class="mt-5 flex justify-center"
            @click.prevent="openUpgradeModal()"
            label="Assinar"
          />
        </div>
      </div>
      <img
        src="/img/pages/forms/blurred_summary.png"
        alt="Gráfico de exemplo"
        class="mx-auto w-full filter blur-md z-0 pointer-events-none"
      >
    </div>
  </div>
</template>

<script setup>
import PlanTag from "~/components/app/PlanTag.vue"
import FormSummary from "~/components/open/forms/components/FormSummary.vue"

const props = defineProps({
  form: { type: Object, required: true },
})

definePageMeta({
  middleware: "auth",
})

useOpnSeoMeta({
  title: props.form ? "Resumo - " + props.form.title : "Resumo",
})

const { openSubscriptionModal } = useAppModals()
const { hasFeature } = usePlanFeatures()
const canAccessSummary = computed(() => hasFeature('form_summary'))

const openUpgradeModal = () => {
  openSubscriptionModal({
    plan: 'pro',
    modal_title: 'Faça upgrade para o Pro e tenha resumos dos formulários',
    modal_description: 'Tenha gráficos, estatísticas e insights de todas as respostas dos seus formulários com o plano Pro.',
  })
}
</script>
