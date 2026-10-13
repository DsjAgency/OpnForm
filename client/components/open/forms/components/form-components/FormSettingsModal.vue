<template>
  <SettingsModal
    v-model="isOpen"
    v-model:activeTab="activeTab"
    @close="closeModal"
  >
    <SettingsModalPage
      id="general"
      label="Geral"
      icon="i-heroicons-information-circle"
    >
      <FormInformation />
    </SettingsModalPage>

    <SettingsModalPage
      id="submission"
      label="Envio de respostas"
      icon="i-heroicons-paper-airplane"
    >
      <FormSubmissionSettings />
    </SettingsModalPage>

    <SettingsModalPage
      id="security"
      label="Segurança e acesso"
      icon="i-heroicons-shield-check"
    >
      <FormSecurityAccess />
    </SettingsModalPage>

    <SettingsModalPage
      id="seo"
      label="SEO e redes sociais"
      icon="i-heroicons-link"
    >
      <FormCustomSeo />
    </SettingsModalPage>

    <SettingsModalPage
      id="analytics"
      label="Análises"
      icon="i-heroicons-chart-bar"
    >
      <FormAnalyticsSettings />
    </SettingsModalPage>

    <SettingsModalPage
      id="custom-code"
      label="Código personalizado"
      icon="i-heroicons-code-bracket"
    >
      <FormCustomCode />
    </SettingsModalPage>

    <SettingsModalPage
      id="variables"
      label="Variáveis"
      icon="i-heroicons-variable"
    >
      <ComputedVariablesTab :edit-request="computedVariableEditRequest" />
    </SettingsModalPage>

  </SettingsModal>
</template>

<script setup>
import SettingsModal from '~/components/pages/settings/SettingsModal.vue'
import SettingsModalPage from '~/components/pages/settings/SettingsModalPage.vue'

const FormInformation = defineAsyncComponent(() => import('~/components/open/forms/components/form-components/FormInformation.vue'))
const FormSubmissionSettings = defineAsyncComponent(() => import('~/components/open/forms/components/form-components/FormSubmissionSettings.vue'))
const FormSecurityAccess = defineAsyncComponent(() => import('~/components/open/forms/components/form-components/FormSecurityAccess.vue'))
const FormCustomSeo = defineAsyncComponent(() => import('~/components/open/forms/components/form-components/FormCustomSeo.vue'))
const FormCustomCode = defineAsyncComponent(() => import('~/components/open/forms/components/form-components/FormCustomCode.vue'))
const ComputedVariablesTab = defineAsyncComponent(() => import('~/components/open/forms/components/computed-variables/ComputedVariablesTab.vue'))
const FormAnalyticsSettings = defineAsyncComponent(() => import('~/components/open/forms/components/form-components/FormAnalyticsSettings.vue'))

const emit = defineEmits(['close', 'update:activeTab'])

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  activeTab: {
    type: String,
    default: 'general'
  },
  computedVariableEditRequest: {
    type: Object,
    default: null
  }
})

// Modal state
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('close', value)
})

// Active tab state
const activeTab = computed({
  get: () => props.activeTab,
  set: (value) => emit('update:activeTab', value)
})

// Methods
const closeModal = () => {
  isOpen.value = false
}

// Define keyboard shortcuts
defineShortcuts({
  escape: {
    handler: () => {
      closeModal()
    }
  }
})
</script>
