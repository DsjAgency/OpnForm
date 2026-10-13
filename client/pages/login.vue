<template>
  <div class="min-h-screen bg-white lg:grid lg:grid-cols-[1.05fr_1fr]">
    <!-- Brand panel -->
    <aside class="relative hidden overflow-hidden bg-gradient-to-br from-primary-700 via-primary-800 to-primary-950 lg:flex lg:flex-col lg:justify-between p-12 text-white">
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-24 -top-24 size-96 rounded-full bg-white/5 blur-2xl" />
        <div class="absolute -bottom-32 -right-20 size-[28rem] rounded-full bg-[#f1c6a8]/10 blur-3xl" />
        <div class="absolute inset-0 opacity-[0.07] [background-image:radial-gradient(white_1px,transparent_1px)] [background-size:22px_22px]" />
      </div>

      <div class="relative flex items-center gap-3">
        <span class="flex size-11 items-center justify-center rounded-xl bg-white shadow-lg">
          <img src="/img/forms4u-icon.png" alt="" class="size-8">
        </span>
        <span class="text-xl font-semibold tracking-tight !text-white">Forms4U</span>
      </div>

      <div class="relative max-w-md">
        <img
          src="/img/forms4u-icon.png"
          alt=""
          class="mb-10 size-36 drop-shadow-2xl f4u-float"
        >
        <h1 class="!text-4xl !font-semibold leading-tight tracking-tight !text-white">
          Formulários da Cidadania4U, num só lugar.
        </h1>
        <p class="mt-4 text-lg !text-white/75">
          Crie, compartilhe e acompanhe as respostas dos seus formulários com a cara da nossa marca.
        </p>
        <ul class="mt-8 space-y-3">
          <li
            v-for="(item, index) in highlights"
            :key="item"
            class="flex items-center gap-3 !text-white/90 f4u-fade-up"
            :style="{ animationDelay: `${200 + index * 120}ms` }"
          >
            <span class="flex size-6 items-center justify-center rounded-full bg-[#f1c6a8]/20">
              <UIcon name="i-heroicons-check" class="size-3.5 text-[#f1c6a8]" />
            </span>
            {{ item }}
          </li>
        </ul>
      </div>

      <p class="relative text-sm !text-white/50">
        © {{ year }} Cidadania4U
      </p>
    </aside>

    <!-- Form -->
    <main class="relative flex min-h-screen items-center justify-center px-6 py-12 sm:px-10">
      <div class="pointer-events-none absolute inset-0 lg:hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-primary-50 via-white to-white" />
      </div>

      <div class="relative w-full max-w-md f4u-fade-up">
        <img
          src="/img/forms4u-logo.png"
          alt="Forms4U"
          class="mb-10 h-12 w-auto"
        >
        <h2
          data-testid="login-page"
          class="!text-3xl !font-semibold tracking-tight text-neutral-950"
        >
          Entrar no Forms4U
        </h2>
        <p class="mt-2 text-neutral-500">
          Que bom ver você de novo! Use seu e-mail da Cidadania4U.
        </p>

        <div class="mt-8">
          <LoginForm />
        </div>

        <p class="mt-10 flex items-center gap-2 text-xs text-neutral-400">
          <UIcon name="i-heroicons-lock-closed" class="size-3.5" />
          Acesso restrito à equipe da Cidadania4U.
        </p>
      </div>
    </main>
  </div>
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

const year = new Date().getFullYear()
const highlights = [
  "Formulários bonitos e fáceis de responder",
  "Respostas organizadas em tempo real",
  "Integrações e notificações automáticas",
]
</script>
