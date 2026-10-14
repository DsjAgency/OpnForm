<template>
  <div>
    <TrackClick
      name="share_embed_form_popup_click"
      :properties="{
        form_id: form.id,
        form_slug: form.slug,
      }"
    >
      <UButton
        variant="outline"
        color="neutral"
        icon="i-heroicons-chat-bubble-bottom-center-text"
        @click="onOpenClick"
      >
        Incorporar como pop-up
      </UButton>
    </TrackClick>

    <UModal
      v-model:open="isModalOpen"
      :ui="{ content: 'sm:max-w-2xl' }"
      title="Adicione o pop-up ao seu site"
      :content="{
        onPointerDownOutside: (event) => { if (event.target?.closest('.nf-main')) {return event.preventDefault()}}
      }"
    >
      <template #body>
        <h3 class="text-xl font-semibold mb-2">
          Demo
        </h3>
        <p class="pb-6">
          Uma prévia do pop-up do formulário foi adicionada a esta página.
          <span class="font-semibold text-primary-900">Clique no botão no canto inferior
            {{ advancedOptions.position }} corner to try it</span>.
        </p>

        <h3 class="border-t text-xl font-semibold mb-2 pt-6">
          Como funciona?
        </h3>
        <p>
          Cole o trecho de código abaixo na seção <b>&lt;head&gt;</b>
          do seu site.
        </p>

        <div class="border border-primary-300 bg-primary-50 dark:bg-notion-dark-light rounded-md p-4 mb-5 w-full mx-auto mt-4 select-all">
          <div class="flex items-center">
            <p class="select-all text-primary-700 flex-grow break-all">
              {{ embedPopupCode }}
            </p>
            <div
              class="hover:bg-primary-100 rounded-sm transition-colors cursor-pointer"
              @click="copyToClipboard"
            >
                <Icon 
                  name="heroicons:clipboard-document" 
                  class="h-6 w-6 text-primary-700" 
                />
            </div>
          </div>
        </div>

        <div class="border-t my-4" />
        <VForm size="sm">
          <h3 class="text-xl font-semibold mb-2">
            Personalização
          </h3>
          
          <ColorInput
            v-model="advancedOptions.bgcolor"
            name="bgcolor"
            label="Cor de fundo do círculo"
          />
          <TextInput
            v-model="advancedOptions.emoji"
            name="emoji"
            class="mt-4 max-w-xs"
            label="Emoji"
            :max-char-limit="2"
          />
          <FlatSelectInput
            v-model="advancedOptions.position"
            name="position"
            class="mt-4 max-w-xs"
            label="Posição"
            :options="[
              { name: 'Inferior direito', value: 'right' },
              { name: 'Inferior esquerdo', value: 'left' },
            ]"
          />
          <TextInput
            v-model="advancedOptions.width"
            name="width"
            class="mt-4 max-w-xs"
            label="Largura máxima do pop-up (px)"
            native-type="number"
          />
        </VForm>
      </template>
    </UModal>
  </div>
</template>

<script setup>
import { ref, defineProps, computed } from "vue"
import { appUrl } from "~/lib/utils.js"
import TrackClick from "~/components/global/TrackClick.vue"

const { copy } = useClipboard()
const crisp = useCrisp()
const props = defineProps({
  form: { type: Object, required: true },
})

const embedScriptUrl = "/widgets/embed-min.js"
const showEmbedFormAsPopupModal = ref(false)

// Modal state
const isModalOpen = computed({
  get() {
    return showEmbedFormAsPopupModal.value
  },
  set(value) {
    if (!value) {
      onClose()
    }
  }
})

const advancedOptions = ref({
  emoji: "💬",
  position: "right",
  bgcolor: "#3B82F6",
  width: "500",
})

const shareUrl = computed(() => {
  return props.form.share_url
})
const embedPopupCode = computed(() => {
  const nfData = {
    formurl: shareUrl.value,
    emoji: advancedOptions.value.emoji,
    position: advancedOptions.value.position,
    bgcolor: advancedOptions.value.bgcolor,
    width: advancedOptions.value.width,
  }
  previewPopup(nfData)
  return (
    "<script async data-nf='" +
    JSON.stringify(nfData) +
    "' src='" +
    appUrl(embedScriptUrl) +
    "'></scrip" +
    "t>"
  )
})

onMounted(() => {
  advancedOptions.value.bgcolor = props.form.color
})

const onClose = () => {
  removePreview()
  crisp.showChat()
  showEmbedFormAsPopupModal.value = false
}
const onOpenClick = () => {
  const style = props.form?.presentation_style || 'classic'
  if (style === 'focused') {
    useAlert().warning('O modo pop-up ainda não é compatível com formulários no estilo Focado.')
    return
  }
  showEmbedFormAsPopupModal.value = true
}
const copyToClipboard = () => {
  if (import.meta.server) return
  copy(embedPopupCode.value)
  useAlert().success("Copiado!")
}
const removePreview = () => {
  if (import.meta.server) return
  const oldP = document.head.querySelector("#nf-popup-preview")
  if (oldP) {
    oldP.remove()
  }
  const oldM = document.body.querySelector(".nf-main")
  if (oldM) {
    oldM.remove()
  }
}
const previewPopup = (nfData) => {
  if (import.meta.server) return
  if (!showEmbedFormAsPopupModal.value) {
    return
  }

  // Remove old preview, if there
  removePreview()

  // Hide crisp
  crisp.hideChat()

  // Add new preview
  const el = document.createElement("script")
  el.id = "nf-popup-preview"
  el.async = true
  el.src = embedScriptUrl
  el.setAttribute("data-nf", JSON.stringify(nfData))
  document.head.appendChild(el)
}
</script>
