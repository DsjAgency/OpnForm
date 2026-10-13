<template>
  <!--  Forgot password modal  -->
  <UModal
    v-model:open="isOpen"
    :ui="{ content: 'sm:max-w-lg' }"
    :title="isMailSent ? 'Verifique seu e-mail' : 'Esqueceu a senha?'"
    :description="isMailSent ? '' : 'Sem problemas, enviaremos as instruções para redefinir.'"
  >
    <template #body>
      <template v-if="isMailSent">
        <div class="text-center">
          Enviamos um link para redefinir a senha para <br><span class="font-bold">{{ form.email }}</span>
        </div>
        <div class="w-full mt-4 text-center">
          <UButton
            icon="heroicons:arrow-path"
            variant="outline"
            color="neutral"
            label="Reenviar e-mail"
            @click="send"
          />
        </div>
      </template>
      <template v-else>
        <form
          @submit.prevent="send"
        >
          <text-input
            name="email"
            :form="form"
            label="E-mail"
            placeholder="Seu endereço de e-mail"
            :required="true"
          />

          <div class="w-full mt-6">
            <UButton
              type="submit"
              block
              :loading="form.busy"
              label="Redefinir senha"
            />
          </div>
        </form>
      </template>
    </template>

    <template #footer>
      <UButton
        block
        icon="heroicons:arrow-left"
        variant="link"
        color="neutral"
        @click="close"
        label="Voltar para o login"
      />
    </template>
  </UModal>
</template>

<script setup>
const props = defineProps({
  show: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits(['close'])

const isMailSent = ref(false)
const form = useForm({
  email: "",
})

const isOpen = computed({
  get() {
    return props.show
  },
  set(value) {
    if (!value) {
      close()
    }
  }
})

const send = () => {
  form.post("/password/email").then(() => {
    isMailSent.value = true
  }).catch(error => {
    if(error?.data?.email){
      useAlert().error(error.data?.email)
      isMailSent.value = false
    }
  })
}

const close = () => {
  emit("close")
  isMailSent.value = false
}
</script>
