<template>
  <VForm size="sm">
    <div class="space-y-4">
      <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h3 class="text-lg font-medium text-neutral-900">Segurança e acesso</h3>
          <p class="mt-1 text-sm text-neutral-500">
            Defina quem pode acessar o formulário e quando.
          </p>
        </div>
      </div>

      <TextInput
        name="password"
        :form="form"
        class="mt-4 max-w-xs"
        label="Senha do formulário"
        placeholder="********"
        help="Deixe vazio para desativar a proteção por senha"
      />
      <DateInput
        :with-time="true"
        name="closes_at"
        class="mt-4 max-w-xs"
        :form="form"
        label="Data de encerramento"
        help="Deixe vazio para manter o formulário aberto sem prazo"
        :required="false"
      />
      <div
        v-if="form.closes_at || form.visibility == 'closed'"
        class="bg-neutral-50 border rounded-lg px-4 py-2"
      >
        <rich-text-area-input
          name="closed_text"
          :form="form"
          :allow-fullscreen="true"
          label="Texto de formulário encerrado"
          help="Esta mensagem aparece quando o formulário estiver encerrado"
          :required="false"
          wrapper-class="mb-0"
        />
      </div>
      <text-input
        name="max_submissions_count"
        native-type="number"
        :min="1"
        :form="form"
        label="Limitar número de respostas"
        placeholder="Máximo de respostas"
        class="mt-4 max-w-xs"
        help="Deixe vazio para respostas ilimitadas"
        :required="false"
      />
      <div
        v-if="form.max_submissions_count && form.max_submissions_count > 0"
        class="bg-neutral-50 border rounded-lg px-4 py-2"
      >
        <rich-text-area-input
          wrapper-class="mb-0"
          :allow-fullscreen="true"
          name="max_submissions_reached_text"
          :form="form"
          label="Texto de limite de respostas atingido"
          help="Esta mensagem aparece quando o formulário atingir o limite de respostas"
          :required="false"
        />
      </div>

      <h4 class="font-semibold mt-4 border-t pt-4">
        Segurança
      </h4>
      <p class="text-neutral-500 text-sm">
        Proteja seu formulário e seus arquivos confidenciais.
      </p>
      <div
        v-if="hasCaptcha"
        class="flex items-start gap-6 flex-wrap"
      >
        <ToggleSwitchInput
          name="use_captcha"
          :form="form"
          class="mt-4"
          label="Proteção contra robôs"
          help="Protege o formulário contra spam e abuso com um captcha"
        />
        <FlatSelectInput
          v-if="form.use_captcha"
          name="captcha_provider"
          :form="form"
          :options="captchaOptions"
          class="mt-4 w-80"
          label="Selecione um provedor de captcha"
        />
      </div>
    </div>
  </VForm>
</template>

<script setup>
const workingFormStore = useWorkingFormStore()
const { content: form } = storeToRefs(workingFormStore)
const config = useRuntimeConfig()

const hasCaptcha = computed(() => {
  return config.public.hCaptchaSiteKey || config.public.reCaptchaSiteKey
})

const captchaOptions = computed(() => {
  const options = []
  
  if (config.public.reCaptchaSiteKey) {
    options.push({ name: 'reCAPTCHA', value: 'recaptcha' })
  }
  
  if (config.public.hCaptchaSiteKey) {
    options.push({ name: 'hCaptcha', value: 'hcaptcha' })
  }
  
  return options
})
</script>
