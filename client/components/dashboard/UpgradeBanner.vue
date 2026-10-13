<template>
  <UAlert
    v-if="showBanner"
    class="mt-8 p-4"
    icon="i-heroicons-sparkles-solid"
    color="primary"
    variant="subtle"
    title="Conheça o plano Pro"
    description="Remova a marca OpnForm, personalize mais os formulários, use seu domínio, integre com suas ferramentas favoritas, convide usuários e muito mais!"
    :actions="[
      {
        label: 'Fazer upgrade agora',
        onClick: () => openSubscriptionModal({ plan: 'pro', modal_title: 'Upgrade para o plano Pro' })
      },
      {
        label: 'Close',
        color: 'neutral',
        variant: 'outline',
        onClick: dismissBanner
      }
    ]"
  />
</template>

<script setup>
const COOKIE_NAME = 'upgrade_banner_dismissed'
const COOKIE_EXPIRY_DAYS = 7

// Composables
const { openSubscriptionModal } = useAppModals()

// Cookie state
const dismissedCookie = useCookie(COOKIE_NAME, {
  default: () => false,
  maxAge: COOKIE_EXPIRY_DAYS * 24 * 60 * 60 * 1000, // 7 days in milliseconds
  sameSite: 'lax',
  secure: true,
  httpOnly: false
})

// Computed
const { current: workspace } = useCurrentWorkspace()
const isSelfHosted = computed(() => useFeatureFlag('self_hosted'))

// Get current tier from workspace
const currentTier = computed(() => workspace.value?.plan_tier)

const showBanner = computed(() => {
  return (
    !dismissedCookie.value &&
    workspace.value &&
    currentTier.value === 'free' &&
    !isSelfHosted.value
  )
})

const dismissBanner = () => {
  dismissedCookie.value = true
}
</script> 
