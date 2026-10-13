<template>
  <div>
    <TrackClick
      name="regenerate_form_link_click"
      :properties="{form_id: form.id, form_slug: form.slug}"
    >
      <UButton
        variant="outline"
        color="neutral"
        icon="i-heroicons-arrow-path"
        @click="showGenerateFormLinkModal = true"
        label="Gerar novo link"
      />
    </TrackClick>

    <!--  Regenerate form link modal  -->
    <UModal
      v-model:open="isModalOpen"
      :ui="{ content: 'sm:max-w-2xl' }"
    >
      <template #header>
        <div class="flex items-center w-full gap-4 px-2">
          <h2 class="font-semibold">
            Gerar novo link do formulário
          </h2>
        </div>
        <UButton
          color="neutral"
          variant="outline"
          icon="i-heroicons-question-mark-circle"
          size="sm"
          @click="crisp.openHelpdeskArticle('how-to-change-my-form-url-171rjw7')"
        >
          Ajuda
        </UButton>
    </template>

      <template #body>
        <p>
          Você pode escolher entre diferentes formatos de URL para o formulário.
          <span class="font-semibold">Cuidado: mudar a URL do formulário não pode ser
            desfeito</span>. Atualize a URL em todos os lugares onde ela é usada.
        </p>
        <div
          v-if="isSelfHosted"
          class="border-t py-4 mt-4"
        >
          <h3 class="text-xl text-neutral-700 font-semibold">
            URL personalizada
          </h3>
          <p>
            Escolha sua própria URL. Use letras minúsculas, números e hífens (até 100 caracteres).
          </p>
          <form
            class="flex items-start gap-2 mt-4"
            @submit.prevent="setCustomSlug"
          >
            <div class="flex-1">
              <UInput
                v-model="customSlug"
                class="w-full"
                placeholder="my-form-url"
                :color="customSlugError ? 'error' : undefined"
              />
              <p
                v-if="customSlugError"
                class="text-sm text-red-600 mt-1"
              >
                {{ customSlugError }}
              </p>
            </div>
            <TrackClick
              name="regenerate_form_link_custom_click"
              :properties="{form_id: form.id, form_slug: form.slug, type: 'custom'}"
            >
              <UButton
                type="submit"
                :loading="regenerateLinkMutationInstance.isPending.value"
                :disabled="!customSlug"
                variant="outline"
                color="primary"
              >
                Definir URL personalizada
              </UButton>
            </TrackClick>
          </form>
        </div>
        <div class="border-t py-4 mt-4">
          <h3 class="text-xl text-neutral-700 font-semibold">
            URL legível
          </h3>
          <p>
            Se as pessoas vão ver esta URL, talvez seja melhor deixá-la
            bonita e legível. Exemplo:
          </p>
          <p class="text-neutral-600 border p-4 bg-neutral-50 rounded-md mt-4">
            https://opnform.com/forms/contact-form-e68des
          </p>
          <div class="text-center mt-4">
            <TrackClick
              name="regenerate_form_link_readable_click"
              :properties="{form_id: form.id, form_slug: form.slug, type: 'slug'}"
            >
              <UButton
                :loading="regenerateLinkMutationInstance.isPending.value"
                variant="outline"
                color="primary"
                @click="regenerateLink('slug')"
              >
                Gerar URL legível
              </UButton>
            </TrackClick>
          </div>
        </div>
        <div class="border-t pt-4 mt-4">
          <h3 class="text-xl text-neutral-700 font-semibold">
            URL com ID aleatório
          </h3>
          <p>
            Se as pessoas não vão ver a URL do formulário (por exemplo, se ele estiver incorporado)
            e você prefere uma URL aleatória e difícil de adivinhar. Exemplo:
          </p>
          <p class="text-neutral-600 p-4 border bg-neutral-50 rounded-md mt-4">
            https://opnform.com/forms/b4417f9c-34ae-4421-8006-832ee47786e7
          </p>
          <div class="text-center mt-4">
            <TrackClick
              name="regenerate_form_link_random_click"
              :properties="{form_id: form.id, form_slug: form.slug, type: 'uuid'}"
            >
              <UButton
                :loading="regenerateLinkMutationInstance.isPending.value"
                variant="outline"
                color="primary"
                @click="regenerateLink('uuid')"
              >
                Gerar URL com ID aleatório
              </UButton>
            </TrackClick>
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>

<script setup>
import TrackClick from "~/components/global/TrackClick.vue"

const props = defineProps({
  form: { type: Object, required: true },
})

const crisp = useCrisp()
const router = useRouter()
const showGenerateFormLinkModal = ref(false)

// Modal state
const isModalOpen = computed({
  get() {
    return showGenerateFormLinkModal.value
  },
  set(value) {
    showGenerateFormLinkModal.value = value
  }
})

const { regenerateLink: regenerateLinkMutation } = useForms()
const regenerateLinkMutationInstance = regenerateLinkMutation()

const isSelfHosted = computed(() => useFeatureFlag('self_hosted'))
const customSlug = ref("")
const customSlugError = ref("")

const setCustomSlug = () => {
  regenerateLink("custom", { slug: customSlug.value.trim() })
}

const regenerateLink = (option, data = null) => {
  customSlugError.value = ""
  regenerateLinkMutationInstance.mutateAsync({
    id: props.form.id,
    option,
    data
  }).then((data) => {
    router.push({
      name: "forms-slug-show-share",
      params: { slug: data.form.slug },
    })
    useAlert().success(data.message)
    showGenerateFormLinkModal.value = false
  }).catch((error) => {
    const slugError = error?.data?.errors?.slug?.[0]
    if (option === "custom" && slugError) {
      customSlugError.value = slugError
      return
    }
    useAlert().error(error?.data?.message || "Algo deu errado")
  })
}
</script>
