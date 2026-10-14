<template>
  <main
    class="flex min-h-screen items-center justify-center bg-[#6B0F2B] px-4 py-16"
    :style="{
      backgroundImage: 'url(/img/backgrounds/forms4u-vinho.png)',
      backgroundSize: 'cover',
      backgroundPosition: 'left top',
    }"
  >
    <div class="w-full max-w-sm rounded-2xl bg-white p-8 shadow-2xl f4u-fade-up">
      <h1
        data-testid="login-page"
        class="!text-2xl !font-semibold text-neutral-900"
      >
        Entrar
      </h1>
      <p class="mt-1 mb-6 text-sm text-neutral-500">
        Acesse sua conta do Forms4U.
      </p>

      <LoginForm />
    </div>
  </main>
</template>

<script setup>
import LoginForm from "~/components/pages/auth/components/LoginForm.vue"

definePageMeta({
  middleware: "guest",
  layout: "empty",
})

useOpnSeoMeta({
  title: "Entrar",
})
</script>
