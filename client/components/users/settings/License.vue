<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h3 class="text-lg font-medium text-neutral-900">Licença Enterprise</h3>
        <p class="mt-1 text-sm text-neutral-500">
          Gerencie sua licença Enterprise auto-hospedada.
        </p>
      </div>
    </div>

    <div v-if="canAccessEnterprise" class="space-y-4">
      <div class="rounded-lg border border-neutral-200 bg-white p-4 sm:p-5 space-y-4">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-start gap-3">
            <div
              class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full"
              :class="isGracePeriod ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600'"
            >
              <Icon
                :name="isGracePeriod ? 'i-heroicons-clock' : 'i-heroicons-check-circle'"
                class="h-5 w-5"
              />
            </div>
            <div>
              <p class="text-sm font-semibold text-neutral-900">
                {{ isGracePeriod ? 'Licença em período de carência' : 'Licença ativada' }}
              </p>
              <p class="mt-1 text-sm text-neutral-600">
                {{ isGracePeriod ? 'Ative uma chave válida para não perder os recursos Enterprise.' : 'Sua licença Enterprise está ativa e todos os recursos licenciados estão liberados.' }}
              </p>
            </div>
          </div>
        </div>
      </div>    </div>

    <div v-else class="space-y-4">
      <div class="rounded-lg border p-4 sm:p-5 space-y-4" :class="inactiveCardClasses">
        <div class="flex items-start gap-3">
          <div
            class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full"
            :class="inactiveIconClasses"
          >
            <Icon
              :name="inactiveIcon"
              class="h-5 w-5"
            />
          </div>
          <div>
            <p class="text-sm font-semibold text-neutral-900">
              {{ inactiveTitle }}
            </p>
            <p class="mt-1 text-sm text-neutral-600">
              {{ inactiveDescription }}
            </p>
          </div>
        </div>
      </div>

      <div class="space-y-2">
        <TextInput
          label="Chave de licença"
          name="license_key"
          :form="licenseKeyForm"
          placeholder="lic_xxxxxxxxxxxxxxxxxxxxxxxx"
        />
        <UButton
          :loading="activating"
          :disabled="!licenseKeyForm.license_key || activating"
          icon="i-heroicons-check-circle"
          @click="activateLicense"
        >
          Ativar licença
        </UButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { licenseApi } from '~/api'
import { useInstanceLicense } from '~/composables/useInstanceLicense'

const alert = useAlert()
const { invalidateFlags } = useFeatureFlags()
const { invalidateUser } = useAuth()

const {
  hasLicense,
  canAccessEnterprise,
  isGracePeriod,
  isExpired,
  isActivationLimitReached,
} = useInstanceLicense()

const activating = ref(false)
const licenseKeyForm = useForm({
  license_key: '',
})

const inactiveCardClasses = computed(() => {
  return hasLicense.value ? 'border-red-200 bg-red-50/40' : 'border-neutral-200 bg-white'
})

const inactiveIconClasses = computed(() => {
  return hasLicense.value ? 'bg-red-100 text-red-600' : 'bg-neutral-100 text-neutral-500'
})

const inactiveIcon = computed(() => {
  return hasLicense.value ? 'i-heroicons-exclamation-triangle' : 'i-heroicons-key'
})

const inactiveTitle = computed(() => {
  if (isActivationLimitReached.value) return 'Licença já ativada'
  if (isExpired.value) return 'Licença expirada'
  if (hasLicense.value) return 'Licença inativa'
  return 'Nenhuma licença ativa'
})

const inactiveDescription = computed(() => {
  if (isActivationLimitReached.value) {
    return 'Esta chave de licença já está ativada em outra instância auto-hospedada. Fale com o suporte para reiniciá-la ou ative outra chave.'
  }
  if (isExpired.value) {
    return 'Os recursos Enterprise estão desativados. Renove ou ative uma chave de licença válida para recuperar o acesso.'
  }
  if (hasLicense.value) {
    return 'Os recursos Enterprise estão desativados. Ative uma chave de licença válida para recuperar o acesso.'
  }
  return 'Digite sua chave de licença abaixo para liberar os recursos Enterprise nesta instância auto-hospedada.'
})

const activateLicense = () => {
  activating.value = true
  licenseApi.activate(licenseKeyForm.license_key).then((result) => {
    if (result.status === 'active') {
      alert.success(result.message)
      return invalidateFlags().then(() => invalidateUser())
    } else {
      alert.error(result.message)
    }
  }).catch((error) => {
    alert.error(error?.data?.message || 'Não foi possível ativar a licença. Confira a chave.')
  }).finally(() => {
    activating.value = false
  })
}
</script>
