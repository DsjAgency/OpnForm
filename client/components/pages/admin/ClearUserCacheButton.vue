<template>
  <UButton
    color="gray"
    variant="outline"
    :loading="loading"
    label="Limpar cache"
    icon="i-heroicons-arrow-path-20-solid"
    @click="clearUserCache"
  />
</template>

<script setup>
import { adminApi } from '~/api'

const props = defineProps({
  user: { type: Object, required: true }
})
const emit = defineEmits(['cache-cleared'])

const alert = useAlert()
const loading = ref(false)

async function clearUserCache() {
  if (!props.user?.id) return
  loading.value = true
  try {
    const response = await adminApi.clearUserCache({ user_id: props.user.id })
    alert.success(response.message || 'Cache do usuário limpo.')
    emit('cache-cleared')
  } catch (error) {
    alert.error(error.data?.message || 'Ocorreu um erro.')
  } finally {
    loading.value = false
  }
}
</script>
