<template>
  <VForm size="sm">
    <div class="space-y-4">
      <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h3 class="text-lg font-medium text-neutral-900">
            SEO e redes sociais - Meta <PlanTag
            class="ml-2"
            feature="seo_meta"
            upgrade-modal-title="Upgrade to Enhance Your Form's SEO"
            upgrade-modal-description="Explore advanced SEO features in the editor on our Free plan. Upgrade to fully implement custom meta tags, Open Graph data, and improved search visibility. Boost your form's online presence and attract more respondents with our premium SEO toolkit."
          />
          </h3>
          <p class="mt-1 text-sm text-neutral-500">
            Personalize a imagem e o texto que aparecem quando o formulário é compartilhado em outros sites (Open Graph).
          </p>
        </div>
      <UButton
        label="Ajuda"
        icon="i-heroicons-question-mark-circle"
        variant="outline"
        color="neutral"
        @click="crisp.openHelpdeskArticle('how-do-i-add-custom-seo-settings-to-my-forms-url-preview-1v9y9a')"
      />
      </div>

      <template v-if="form.seo_meta">
        <div class="flex flex-col lg:flex-row gap-8 mt-4 lg:items-start">
          <!-- Left Column - Form Inputs -->
          <div class="flex-1 space-y-4 max-w-xs">
            <SelectInput
              v-if="useFeatureFlag('custom_domains')"
              v-model="form.custom_domain"
              :clearable="true"
              :disabled="customDomainOptions.length <= 0"
              :options="customDomainOptions"
              name="type"
              label="Domínio do formulário"
              placeholder="yourdomain.com"
            />
            <text-input
              v-model="form.seo_meta.page_title"
              name="page_title"
              label="Título da página"
              help="Recomendado no máximo 60 caracteres"
            />
            <text-area-input
              v-model="form.seo_meta.page_description"
              name="page_description"
              label="Descrição da página"
              help="Entre 150 e 160 caracteres"
            />
            <image-input
              v-model="form.seo_meta.page_thumbnail"
              name="page_thumbnail"
              label="Imagem de miniatura"
              help="og:image - 1200px X 630px"
            />
            <image-input
              v-model="form.seo_meta.page_favicon"
              name="page_favicon"
              label="Favicon"
              help="Favicon da página pública do formulário"
            />
          </div>
          
          <!-- Right Column - Preview (After fields on mobile) -->
          <SeoPreview :form="form" />
        </div>
      </template>

      <div class="w-full border-t pt-4 mt-4">
        <h4 class="font-semibold">
          Privacidade do link
        </h4>
        <p class="text-neutral-500 text-sm mb-4">
          Desative para impedir que o Google mostre o formulário nos resultados de busca.
        </p>
        <ToggleSwitchInput
          name="can_be_indexed"
          :form="form"
          label="Indexável pelo Google"
        />
      </div>

      <div v-if="useFeatureFlag('self_hosted')" class="w-full border-t pt-4 mt-4">
        <h4 class="font-semibold">
          URL personalizada do formulário
        </h4>
        <p class="text-neutral-500 text-sm mb-4">
          Crie uma URL personalizada para o formulário. Ela será o identificador único no endereço do formulário.
        </p>
        <text-input
          :form="form"
          name="slug"
          class="mt-4 max-w-xs"
          label="URL personalizada do formulário"
          help="Use apenas letras minúsculas, números e hífens. Exemplo: meu-formulario"
        />
      </div>
    </div>
  </VForm>
</template>

<script setup>
const crisp = useCrisp()
import PlanTag from "~/components/app/PlanTag.vue"
import SeoPreview from "~/components/open/forms/components/SeoPreview.vue"

const workingFormStore = useWorkingFormStore()
const { content: form } = storeToRefs(workingFormStore)

const { current: workspace } = useCurrentWorkspace()

const customDomainOptions = computed(() => {
  return workspace?.value?.custom_domains
    ? workspace?.value?.custom_domains.map((domain) => {
        return {
          name: domain,
          value: domain,
        }
      })
    : []
})

onMounted(() => {
  if (!form.value.seo_meta || Array.isArray(form.value.seo_meta))
    form.value.seo_meta = {}

  form.value.seo_meta = {
    ...form.value.seo_meta,
    page_title: form.value.seo_meta.page_title === undefined ? null : form.value.seo_meta.page_title,
    page_description: form.value.seo_meta.page_description === undefined ? null : form.value.seo_meta.page_description,
    page_thumbnail: form.value.seo_meta.page_thumbnail === undefined ? null : form.value.seo_meta.page_thumbnail,
    page_favicon: form.value.seo_meta.page_favicon === undefined ? null : form.value.seo_meta.page_favicon,
  }

  if (form.value.custom_domain && workspace.value?.custom_domains && !workspace.value.custom_domains.find((item) => { return item === form.value.custom_domain })) {
    form.value.custom_domain = null
  }
})
</script>
