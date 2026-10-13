<template>
  <div class="min-h-screen bg-neutral-50 flex flex-col justify-center sm:px-6 lg:px-8 py-10">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center items-center mb-6">
        <h1>
          <img
            src="/img/forms4u-logo.png"
            alt="Forms4U"
            class="h-16 w-auto"
          >
        </h1>
      </div>
      
      <p class="mt-2 text-center text-sm text-neutral-600">
        Bem-vindo ao Forms4U! Vamos configurar tudo. Crie sua conta de administrador para começar a criar formulários.
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow-sm sm:rounded-sm sm:px-10">
        <RegisterForm 
          :is-quick="false"
          :is-setup="true"
          @registered="handleSetupComplete"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import RegisterForm from '~/components/pages/auth/components/RegisterForm.vue'

const { invalidateFlags } = useFeatureFlags()
const router = useRouter()

// Check if setup is required
const setupRequired = useFeatureFlag('setup_required', false)
const selfHosted = useFeatureFlag('self_hosted', false)

// Show 404 if setup not required or not self-hosted
if (!setupRequired || !selfHosted) {
  throw createError({ statusCode: 404, statusMessage: 'Página não encontrada' })
}

// SEO
useOpnSeoMeta({
  title: "Configuração inicial",
  description: "Configure sua instância do Forms4U",
  robots: "noindex, nofollow"
})

definePageMeta({
  layout: 'empty'
})

// Handle successful setup completion
const handleSetupComplete = async () => {
  // Invalidate feature flags to update setup_required status
  await invalidateFlags()
  
  // Show success message
  useAlert().success({
    title: "Configuração concluída! 🎉",
    description: "Sua instância do Forms4U está pronta. Hora de criar seu primeiro formulário!"
  })
  
  // Redirect to dashboard
  router.push({ name: "home" })
}
</script> 