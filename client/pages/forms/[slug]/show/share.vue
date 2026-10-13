<template>
  <PageContainer spacing="sm">
    <PageSection
      title="Compartilhar"
      description="Compartilhe seu formulário e incorpore-o no seu site."
    >
      <!-- Status Warning Alert -->
      <UAlert
        v-if="form.visibility === 'draft'"
        icon="i-heroicons-information-circle"
        color="warning"
        variant="subtle"
        title="Formulário em rascunho"
        description="Só você consegue abrir o link enquanto estiver logado. Visitantes não terão acesso a este formulário."
      />

      <UAlert
        v-else-if="form.visibility === 'closed'"
        icon="i-heroicons-lock-closed"
        color="warning"
        variant="subtle"
        title="Formulário encerrado"
        description="As pessoas conseguem abrir o formulário, mas ele não aceita novas respostas."
      />

      <!-- Primary Section: Share Link -->
      <DashboardPanel padding="sm">
        <div class="space-y-4">
          <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-xl grow font-semibold">
              Compartilhe seu formulário
            </h2>
            <div class="flex gap-2">
              <AdvancedFormUrlSettingsPopover
                v-model="shareFormConfig"
                :form="props.form"
              />
            </div>
          </div>
          <p class="text-neutral-600 text-sm">
            Compartilhe o formulário com qualquer pessoa copiando este link. Use em redes sociais,
            mensagens ou e-mail para alcançar seu público.
          </p>

          <CopyContent
            :content="share_url"
            :is-draft="form.visibility == 'draft'"
            class="w-full"
          />

          <div class="flex gap-2 pt-2">
            <SocialShareButton
              :form="props.form"
              :share-url="share_url"
            />

            <FormQrCode
              :form="props.form"
              :extra-query-param="shareUrlForQueryParams"
            />
          </div>
        </div>
      </DashboardPanel>

      <div class="py-4 flex gap-2">
        <UrlFormPrefill
          :form="props.form"
          :extra-query-param="shareUrlForQueryParams"
        />
        <RegenerateFormLink
          :form="props.form"
        />
      </div>

      <!-- Secondary Section: Embed -->
      <DashboardPanel padding="sm">
        <div class="space-y-4">
          <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-xl grow font-semibold">
              Incorporar formulário
            </h2>
            <div class="flex gap-2">
              <UButton
                label="Documentação do SDK"
                icon="i-heroicons-code-bracket"
                variant="outline"
                color="neutral"
                to="https://docs.opnform.com/embedding/javascript-sdk"
                target="_blank"
              />
            </div>
          </div>
          <p class="text-neutral-600 text-sm">
            Incorpore o formulário no seu site copiando o código HTML abaixo.
          </p>

          <EmbedCode
            :form="props.form"
            :extra-query-param="shareUrlForQueryParams"
          />

          <EmbedFormAsPopupModal
            :form="props.form"
            class="max-w-fit"
          />
        </div>
      </DashboardPanel>
    </PageSection>
  </PageContainer>
</template>

<script setup>
import PageContainer from "~/components/dashboard/PageContainer.vue"
import PageSection from "~/components/dashboard/PageSection.vue"
import DashboardPanel from "~/components/dashboard/DashboardPanel.vue"
import EmbedCode from "~/components/pages/forms/show/EmbedCode.vue"
import FormQrCode from "~/components/pages/forms/show/FormQrCode.vue"
import UrlFormPrefill from "~/components/pages/forms/show/UrlFormPrefill.vue"
import AdvancedFormUrlSettingsPopover from "~/components/pages/forms/show/AdvancedFormUrlSettingsPopover.vue"
import SocialShareButton from "~/components/pages/forms/show/SocialShareButton.vue"
import EmbedFormAsPopupModal from "~/components/pages/forms/show/EmbedFormAsPopupModal.vue"
import CopyContent from "~/components/open/forms/components/CopyContent.vue"

const props = defineProps({
  form: { type: Object, required: true },
})

definePageMeta({
  middleware: "auth",
})

useOpnSeoMeta({
  title: props.form ? "Compartilhar - " + props.form.title : "Compartilhar",
})

const shareFormConfig = ref({
  auto_submit: false,
})

const shareUrlForQueryParams = computed(() => {
  let queryStr = ""
  for (const [key, value] of Object.entries(shareFormConfig.value)) {
    if (value && value !== "false" && value !== false) {
      queryStr +=
        "&" + encodeURIComponent(key) + "=" + encodeURIComponent(value)
    }
  }
  return queryStr.slice(1)
})

const share_url = computed(() => {
  return shareUrlForQueryParams.value
    ? props.form.share_url + "?" + shareUrlForQueryParams.value
    : props.form.share_url + shareUrlForQueryParams.value
})
</script>
