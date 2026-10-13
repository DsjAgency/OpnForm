<template>
  <IntegrationWrapper
    v-model="props.integrationData"
    :integration="props.integration"
    :form="form"
  >
    <p class="text-neutral-500 text-sm mb-3">
      Você pode <a
        class="underline cursor-pointer"
        @click="openEmailsModal"
      >
        usar o recurso de SMTP personalizado
      </a> para enviar e-mails do seu próprio domínio.
    </p>

    <MentionInput
      :form="integrationData"
      :mentions="form.properties"
      :computed-variables="form.computed_variables"
      :disable-mention="!canUseAdvancedEmail"
      :disabled="!canUseAdvancedEmail"
      name="data.send_to"
      required
      label="Enviar para"
    >
      <template #help>
        <InputHelp>
        <span v-if="canUseAdvancedEmail">
          Adicione um e-mail por linha
        </span>
        <span v-else>
          Você só pode enviar notificações para o seu próprio e-mail. 
          <a
            class="underline cursor-pointer"
            @click="openSubscriptionModal"
          >Faça upgrade para o plano Pro</a> para enviar a outros endereços.
        </span>
        </InputHelp>
      </template>
    </MentionInput> 
    <div class="flex space-x-4 mt-4">
      <MentionInput
        :form="integrationData"
        :mentions="form.properties"
        :computed-variables="form.computed_variables"
        name="data.sender_name"
        label="Nome do remetente"
        class="flex-1"
      />
      <text-input
        v-if="selfHosted"
        :form="integrationData"
        name="data.sender_email"
        label="E-mail do remetente"
        help="Se o provedor de e-mail permitir; caso contrário, usa o padrão"
        class="flex-1"
      />
    </div>
    <MentionInput
      :form="integrationData"
      :mentions="form.properties"
      :computed-variables="form.computed_variables"
      required
      name="data.subject"
      label="Assunto"
    />
    <rich-text-area-input
      :form="integrationData"
      :enable-mentions="true"
      :enable-image="true"
      :mentions="form.properties"
      :computed-variables="form.computed_variables"
      name="data.email_content"
      label="Conteúdo do e-mail"
      class="mt-4"
    />
    <collapse
      v-model="showEmailAppearance"
      class="mt-4 w-full border rounded-lg bg-gray-50 dark:bg-neutral-900 pr-4"
    >
      <template #title>
        <div class="flex gap-x-3 items-start pr-12 p-4">
          <div
            class="transition-colors"
            :class="{
              'text-blue-600': showEmailAppearance,
              'text-gray-300 dark:text-neutral-500': !showEmailAppearance,
            }"
          >
            <Icon
              name="heroicons:paint-brush-16-solid"
              size="24"
            />
          </div>
          <div class="grow">
            <h4 class="font-semibold flex items-center gap-2">
              Aparência do e-mail
              <PlanTag
                feature="branding.advanced"
                upgrade-modal-title="Upgrade to customise email appearance"
              />
            </h4>
            <p class="text-gray-400 dark:text-neutral-500 text-xs">
              Logo, fontes e cores das notificações por e-mail
            </p>
          </div>
        </div>
      </template>
      <div class="border-t dark:border-neutral-700 p-4 space-y-4">
        <div
          v-if="!canUseAdvancedBranding"
          class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-100"
        >
          A personalização da aparência do e-mail faz parte do plano Business.
          <a
            class="underline cursor-pointer"
            @click="openSubscriptionModal"
          >
            Faça upgrade para o Business
          </a>
          para adicionar seu logo, fontes e cores.
        </div>
        <image-input
          :form="integrationData"
          :disabled="!canUseAdvancedBranding"
          name="data.logo_url"
          label="Logo"
          help="Mostra seu logo no cabeçalho do e-mail (substitui o nome do app)"
        />
        <div class="grid grid-cols-2 gap-4 mt-4">
          <div>
            <label class="text-neutral-700 dark:text-neutral-300 font-semibold text-xs mb-2 block">Fonte</label>
            <UButton
              color="neutral"
              block
              size="lg"
              variant="outline"
              :disabled="!canUseAdvancedBranding"
              @click="showGoogleFontPicker = true"
            >
              <span :style="{ 'font-family': (integrationData.data.font_family ? integrationData.data.font_family + ', sans-serif' : null) }">
                {{ integrationData.data.font_family || 'Default' }}
              </span>
            </UButton>
            <GoogleFontPicker
              :show="showGoogleFontPicker"
              :font="integrationData.data.font_family || null"
              @close="showGoogleFontPicker = false"
              @apply="onApplyFont"
            />
          </div>
          <ColorInput
            :form="integrationData"
            :disabled="!canUseAdvancedBranding"
            name="data.font_color"
            label="Cor do texto"
            help="Cor do texto no e-mail"
          />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <ColorInput
            :form="integrationData"
            :disabled="!canUseAdvancedBranding"
            name="data.outer_background_color"
            label="Cor de fundo externa"
            help="Fundo ao redor da área de conteúdo do e-mail"
          />
          <ColorInput
            :form="integrationData"
            :disabled="!canUseAdvancedBranding"
            name="data.inner_background_color"
            label="Cor de fundo interna"
            help="Fundo da área de conteúdo do e-mail"
          />
        </div>
      </div>
    </collapse>

    <toggle-switch-input
      :form="integrationData"
      name="data.include_submission_data"
      class="mt-4"
      label="Incluir dados da resposta"
      help="Se ativado, o e-mail terá as respostas enviadas no formulário"
    />
    <p
      v-if="integrationData.data.include_submission_data"
      class="mt-2 text-xs text-neutral-500"
    >
      Os links de arquivos enviados seguem a política de segurança do espaço de trabalho ({{ externalFileLinkExpirationLabel }}).
      <a
        class="cursor-pointer underline"
        @click="openWorkspaceSettings('external-file-links')"
      >
        Alterar política
      </a>
    </p>
    <toggle-switch-input
      v-if="integrationData.data.include_submission_data"
      :form="integrationData"
      name="data.include_hidden_fields_submission_data"
      class="mt-4"
      label="Incluir campos ocultos"
      help="Se ativado, o e-mail terá os campos ocultos"
    />
    <toggle-switch-input
      v-if="integrationData.data.include_submission_data"
      :form="integrationData"
      name="data.embed_uploaded_images"
      class="mt-4"
      label="Mostrar imagens enviadas no corpo do e-mail"
      help="Mostra imagens GIF, JPEG e PNG de até 6 MB direto no e-mail. As demais imagens continuam como links seguros. Imagens incorporadas ficam disponíveis no e-mail do destinatário."
    />
    <toggle-switch-input
      v-if="form.editable_submissions"
      :form="integrationData"
      name="data.link_edit_submission"
      class="mt-4"
      label="Link para editar a resposta"
    />
     
    <SelectInput
      :form="integrationData"
      name="data.pdf_template_ids"
      :options="pdfTemplateOptions"
      multiple
      clearable
      class="mt-4"
      label="Anexar modelos de PDF"
      help="Gera PDFs a partir dos modelos selecionados e anexa ao e-mail. Deixe vazio para não anexar nenhum PDF."
    />

    <MentionInput
      :form="integrationData"
      :mentions="form.properties"
      :computed-variables="form.computed_variables"
      class="mt-4"
      name="data.reply_to"
      label="Responder para"
      help="Se vazio, as respostas irão para o seu próprio e-mail."
    />
  </IntegrationWrapper>
</template>

<script setup>
import { usePdfTemplates } from '~/composables/query/forms/usePdfTemplates'
import IntegrationWrapper from "./components/IntegrationWrapper.vue"
import GoogleFontPicker from "~/components/open/editors/GoogleFontPicker.vue"
import Collapse from "~/components/app/Collapse.vue"
import PlanTag from "~/components/app/PlanTag.vue"

const props = defineProps({
  integration: { type: Object, required: true },
  form: { type: Object, required: true },
  integrationData: { type: Object, required: true },
  formIntegrationId: { type: Number, required: false, default: null },
})

const selfHosted = computed(() => useFeatureFlag('self_hosted'))
const { openWorkspaceSettings } = useAppModals()
const { data: user } = useAuth().user()

const showEmailAppearance = ref(false)
const showGoogleFontPicker = ref(false)
const workspaceFeatures = computed(() => props.form?.workspace?.features ?? [])
const canUseAdvancedEmail = computed(() => workspaceFeatures.value.includes('integrations.email.advanced'))
const canUseAdvancedBranding = computed(() => workspaceFeatures.value.includes('branding.advanced'))
const externalFileLinkExpirationHours = computed(() => props.form?.workspace?.settings?.external_file_links?.expires_in_hours || 24)
const externalFileLinkExpirationLabel = computed(() => {
  const hours = externalFileLinkExpirationHours.value

  return hours % 24 === 0 ? `${hours / 24} day${hours === 24 ? '' : 's'}` : `${hours} hours`
})

function onApplyFont(val) {
  if (props.integrationData.data) {
    props.integrationData.data.font_family = val
  }
  showGoogleFontPicker.value = false
}

const { list } = usePdfTemplates()
const { data: pdfTemplates } = list(() => props.form?.id)

const pdfTemplateOptions = computed(() => {
  const list = pdfTemplates.value?.data ?? []
  return list.map((t) => ({ name: t.name, value: t.id }))
})

function openEmailsModal () {
  openWorkspaceSettings('emails')
}

function openSubscriptionModal () {
  useAppModals().openSubscriptionModal({
    plan: canUseAdvancedEmail.value ? 'business' : 'pro',
    modal_title: canUseAdvancedEmail.value
      ? 'Faça upgrade para personalizar a aparência do e-mail'
      : 'Faça upgrade para liberar a integração de e-mail completa',
    modal_description: canUseAdvancedEmail.value
      ? 'Faça upgrade para o Business e adicione seu logo, fontes e cores às notificações por e-mail.'
      : 'Faça upgrade para o Pro e personalize os destinatários das notificações, envie e-mail de confirmação para quem respondeu e muito mais: personalização de formulários, domínio próprio, colaboração etc.'
  })
}

onBeforeMount(() => {
  for (const [keyname, defaultValue] of Object.entries({
    send_to: user.value.email || '',
    sender_name: "OpnForm",
    subject: "Recebemos suas respostas",
    email_content: "Olá 👋 <br>Confirmamos que suas respostas foram registradas com sucesso.",
    include_submission_data: true,
    include_hidden_fields_submission_data: false,
    embed_uploaded_images: false,
    logo_url: null,
    font_family: null,
    font_color: null,
    outer_background_color: '#f0f0f0',
    inner_background_color: '#ffffff',
    pdf_template_ids: null,
  })) {
    if (props.integrationData.data[keyname] === undefined) {
      props.integrationData.data[keyname] = defaultValue
    }
  }
})
</script>
