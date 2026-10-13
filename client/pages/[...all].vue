<template>
  <div>
    <div class="flex mt-6">
      <div class="w-full md:w-2/3 md:mx-auto md:max-w-md">
        <img
          alt="Uma planta, já que não temos mais nada para mostrar!"
          src="/img/icons/plant.png"
          class="w-56 mb-5"
        >

        <h1 class="mb-6 font-semibold text-3xl text-neutral-900">
          Página não encontrada (404)
        </h1>

        <div class="links">
          <UButton
            :to="{ name: 'index' }"
            class="hover:underline"
          >
            Voltar ao início
          </UButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: ['root-redirect']
})

useOpnSeoMeta({
  title: "404 - Página não encontrada",
})

const event = useRequestEvent()
setResponseStatus(event, 404, 'Page Not Found')
</script>