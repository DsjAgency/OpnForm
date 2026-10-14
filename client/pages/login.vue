<template>
  <main
    class="flex min-h-screen items-center justify-center bg-[#6B0F2B] px-4 py-16"
    :style="{
      backgroundImage: 'url(/img/backgrounds/forms4u-vinho.png)',
      backgroundSize: 'cover',
      backgroundPosition: 'left top',
    }"
  >
    <div class="login-card relative w-full max-w-[400px] f4u-fade-up">
      <!-- Soft glow behind the card -->
      <div class="pointer-events-none absolute -inset-6 rounded-[2.5rem] bg-[#f1c6a8]/10 blur-2xl" />

      <div class="relative overflow-hidden rounded-3xl bg-white px-8 pb-8 pt-10 shadow-[0_30px_80px_-20px_rgba(20,0,8,0.55)] ring-1 ring-black/5 sm:px-10">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary-900 via-primary-600 to-[#f1c6a8]" />

        <div class="flex flex-col items-center text-center">
          <span class="flex size-14 items-center justify-center rounded-2xl bg-primary-50 ring-1 ring-primary-100 f4u-float">
            <img src="/img/forms4u-icon.png" alt="" class="size-9">
          </span>
          <h1
            data-testid="login-page"
            class="mt-5 !text-2xl !font-semibold tracking-tight text-neutral-900"
          >
            Bem-vindo de volta
          </h1>
          <p class="mt-1.5 text-sm text-neutral-500">
            Entre com sua conta do Forms4U
          </p>
        </div>

        <div class="mt-8">
          <LoginForm />
        </div>
      </div>
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

<style scoped>
/* Taller, softer inputs and a brand gradient button, only on this page */
.login-card :deep(input[type="text"]),
.login-card :deep(input[type="email"]),
.login-card :deep(input[type="password"]) {
  height: 2.875rem;
  border-radius: 0.75rem;
  background-color: var(--color-neutral-50);
  border-color: var(--color-neutral-200);
  transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
}

.login-card :deep(input[type="text"]:focus),
.login-card :deep(input[type="email"]:focus),
.login-card :deep(input[type="password"]:focus) {
  background-color: white;
  border-color: var(--color-cidadania-700);
  box-shadow: 0 0 0 4px color-mix(in srgb, var(--color-cidadania-700) 15%, transparent);
}

/* Browser autofill paints inputs light blue; keep the card's look */
.login-card :deep(input:-webkit-autofill) {
  -webkit-box-shadow: 0 0 0 40px var(--color-neutral-50) inset;
  -webkit-text-fill-color: var(--color-neutral-800);
}

.login-card :deep(label) {
  font-weight: 500;
}

.login-card :deep(button[type="submit"]) {
  height: 2.875rem;
  margin-top: 1rem;
  justify-content: center;
  border-radius: 0.75rem;
  background-image: linear-gradient(135deg, var(--color-cidadania-700), var(--color-cidadania-900));
  box-shadow: 0 10px 24px -10px rgb(74 3 29 / 0.7);
  transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}

.login-card :deep(button[type="submit"]:not(:disabled):hover) {
  transform: translateY(-1px);
  filter: brightness(1.08);
  box-shadow: 0 14px 28px -10px rgb(74 3 29 / 0.75);
}
</style>
