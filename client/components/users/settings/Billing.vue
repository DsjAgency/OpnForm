<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h3 class="text-lg font-medium text-neutral-900">Cobrança</h3>
        <p class="mt-1 text-sm text-neutral-500">
          Gerencie a cobrança. Baixe faturas, altere seu plano ou cancele quando quiser.
        </p>
      </div>
    </div>

    <!-- Billing Management -->
    <template v-if="user.has_customer_id">
      <div class="space-y-4">
        <p
          v-if="usersCount"
          class="text-neutral-600"
        >
          Você tem <span class="font-medium">{{ usersCount }} usuários</span> nos seus espaços de trabalho.
        </p>

        <div class="flex flex-wrap gap-3">
          <UButton
            icon="i-heroicons-credit-card"
            :loading="billingLoading"
            :to="{ name: 'redirect-billing-portal' }"
            target="_blank"
          >

            Cobrança e faturas
          </UButton>
        </div>
      </div>
    </template>

    <!-- AppSumo Billing -->
    <AppSumoBilling />
  </div>
</template>

<script setup>
import AppSumoBilling from "../../vendor/appsumo/AppSumoBilling.vue"
import { billingApi } from '~/api'

const alert = useAlert()

const { data: user } = useAuth().user()
const billingLoading = ref(false)
const usersCount = ref(0)

onMounted(() => {
  loadUsersCount()
})

const loadUsersCount = () => {
      billingApi.getUsersCount()
    .then((data) => {
      usersCount.value = data.count
    })
    .catch((error) => {
      alert.error(error.data.message)
    })
}

</script> 