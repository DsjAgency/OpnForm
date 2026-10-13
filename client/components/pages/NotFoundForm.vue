<template>
  <main class="flex justify-center items-center px-16 w-full max-md:px-5 max-md:max-w-full">
    <section class="flex flex-col mb-4 w-full max-w-[1080px] max-md:mt-10 max-md:max-w-full">
      <div class="text-center mx-auto">
        <img
          src="/img/icons/404.png"
          alt="404 Error"
          class="w-full max-w-[250px] mb-5 mx-auto"
        >
        <h1 class="text-dark text-2xl md:text-4xl leading-none font-medium text-center pt-4 md:pt-0 mx-auto md:max-w-full">
          Desculpe! Este formulário não está mais disponível
        </h1>
        <p class="text-neutral-500 mt-3 max-w-xl mx-auto">
          Ele pode ter sido excluído, desativado, ou o endereço pode ter sido alterado pelo responsável.
        </p>
        
        <h3 class="my-8 text-dark text-xl font-semibold text-center md:pt-0 mx-auto md:max-w-full">
          O que você pode fazer?
        </h3>
        <div class="mt-8 grid grid-cols-2 md:grid-cols-5 gap-4">
          <NuxtLink
            v-for="(action, index) in actions"
            :key="index"
            :to="action.to"
            :href="action.href"
            class="bg-blue-100/50 rounded-lg px-4 py-6 text-center text-neutral-700 hover:bg-blue-100 flex flex-col items-center hover:no-underline"
            @click="action.onClick ? action.onClick() : null"
          >
            <Icon
              :name="action.icon"
              class="w-8 h-8 mb-2 text-blue-500"
            />
            <span class="font-medium">
              {{ action.text }}
            </span>
          </NuxtLink>
        </div>
        
        <div class="mt-10 text-neutral-700">
          <h3 class="mt-8 text-dark text-xl font-semibold text-center md:pt-0 mx-auto md:max-w-full">
            Ainda não sabe o que fazer? Veja outras opções!
          </h3>
          <p class="mt-4 text-neutral-500">
            Sem problemas! Aqui vão algumas opções:
          </p>
          <ul class="mt-4 space-y-2 text-sm">
            <li>
              Quer saber por que as pessoas escolhem o OpnForm? 
              <NuxtLink
                class="text-blue-500 hover:text-blue-600"
                :to="{ name: 'ai-form-builder' }"
              >
                Saiba mais aqui!
              </NuxtLink>
            </li>
            <li>
              Ver modelos
              <NuxtLink
                class="text-blue-500 hover:text-blue-600"
                :to="{ name: 'templates' }"
              >
                Inspire-se com modelos de formulário prontos!
              </NuxtLink>
            </li>
            <li>
              Central de ajuda
              <NuxtLink
                class="text-blue-500 hover:text-blue-600"
                to="https://help.opnform.com/en/"
              >
                Respostas rápidas para dúvidas comuns.
              </NuxtLink>
            </li>
          </ul>
        </div>
      </div>

      <div class="mt-20 border-t">
        <OpenFormFooter />
      </div>
    </section>
  </main>
</template>

<script setup>
const crisp = useCrisp()

const actions = [
  {
    to: { name: 'forms-create-guest' },
    icon: 'i-heroicons-rocket-launch',
    text: 'Criar formulário'
  },
  {
    to: { name: 'login' },
    icon: 'i-heroicons-list-bullet',
    text: 'Encontrar seus formulários'
  },
  {
    href: '#',
    icon: 'i-heroicons-book-open',
    text: 'Documentação',
    onClick: () => crisp.openHelpdesk()
  },
  {
    to: { name: 'index' },
    icon: 'i-heroicons-document-magnifying-glass',
    text: 'Conhecer o OpnForm'
  },
  {
    href: '#',
    icon: 'i-heroicons-chat-bubble-left-right',
    text: 'Precisa de ajuda?',
    onClick: () => crisp.openChat()
  }
]
</script>
