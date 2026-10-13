<template>
  <div class="px-4 pb-4">
    <EditorSectionHeader
      icon="heroicons:paint-brush-16-solid"
      title="Aparência básica"
      :show-line="false"
    />

    <PresentationStyleSwitch />

    <select-input
      name="theme"
      class="mt-4"
      :options="[
        { name: 'Padrão', value: 'default' },
        { name: 'Notion', value: 'notion' },
        { name: 'Simples (sem sombras)', value: 'simple' },
        { name: 'Minimalista', value: 'minimal' },
        { name: 'Transparente', value: 'transparent' }
      ]"
      :form="form"
      label="Tema do formulário"
    />

    <color-input
      name="color"
      :form="form"
      label="Cor de destaque"
      class="my-4"
    >
      <template #label>
        <InputLabel label="">Cor de destaque - <a
          href="#" class="text-blue-500"
          @click.prevent="form.color = DEFAULT_COLOR"
        >Redefinir</a></InputLabel>
      </template>
    </color-input>

    <OptionSelectorInput
      v-model="form.dark_mode"
      :form="form"
      name="dark_mode"
      label="Modo de cor"
      :options="[
        { name: 'auto', label: 'Sistema', icon: 'i-heroicons-computer-desktop' },
        { name: 'light', label: 'Claro', icon: 'i-heroicons-sun' },
        { name: 'dark', label: 'Escuro', icon: 'i-heroicons-moon' },
      ]"
      :multiple="false"
      :columns="3"
      class="mb-4"
    />

    <EditorSectionHeader
      icon="octicon:typography-16"
      title="Texto e idioma"
    />
    <div class="grid grid-cols-2 gap-4">
      <div class="flex-grow my-1" v-if="useFeatureFlag('services.google.fonts')">
        <label class="text-neutral-700 font-semibold text-xs mb-0.5 block">Fonte</label>
        <UButton
          color="neutral"
          variant="outline"
          block
          @click="showGoogleFontPicker = true"
        >
          <span :style="{ 'font-family': (form.font_family ? form.font_family + ' !important' : null) }">
            {{ form.font_family || 'Default' }}
          </span>
        </UButton>
        <GoogleFontPicker
          :show="showGoogleFontPicker"
          :font="form.font_family || null"
          @close="showGoogleFontPicker = false"
          @apply="onApplyFont"
        />
      </div>

      <div class="flex-grow">
        <select-input
          name="language"
          searchable
          :options="availableLocales"
          :form="form"
          label="Idioma"
        />
      </div>
    </div>

    <ToggleSwitchInput
      name="layout_rtl"
      :form="form"
      label="Layout da direita para a esquerda"
    />
    
    <toggle-switch-input
      name="uppercase_labels"
      :form="form"
      label="Rótulos dos campos em maiúsculas"
    />

    <EditorSectionHeader
      icon="heroicons:rectangle-stack-16-solid"
      title="Layout e tamanho"
    />
    <div class="grid grid-cols-2 gap-4">
      <OptionSelectorInput
        seamless
        label="Tamanho dos campos"
        v-model="form.size"
        :form="form"
        name="size"
        :options="[
          { name: 'sm', label:'S'},
          { name: 'md', label:'M' },
          { name: 'lg', label:'L' },
        ]"
        :multiple="false"
        :columns="3"
        class="mb-4"
      />
      <OptionSelectorInput
        v-if="form.theme !== 'transparent'"
        label="Arredondamento dos campos"
        v-model="form.border_radius"
        seamless
        :form="form"
        name="border_radius"
        :options="[
          { name: 'none', icon: 'i-tabler-border-corner-square' },
          { name: 'small', icon: 'i-tabler-border-corner-rounded' },
          { name: 'full', icon: 'i-tabler-border-corner-pill' },
        ]"
        :multiple="false"
        :columns="3"
        class="mb-4"
      />
    </div>

    <OptionSelectorInput
      v-model="form.width"
      label="Largura do formulário"
      :form="form"
      name="width"
      seamless
      v-if="!isFocused"
      :options="[
        { name: 'centered', label: 'Centralizado' },
        { name: 'full', label: 'Largura total' },
      ]"
      :multiple="false"
      :columns="2"
      class="mb-4 w-2/3"
    />

    <EditorSectionHeader
      icon="heroicons:tag-16-solid"
      title="Marca"
    />
    <div class="grid grid-cols-2 gap-4">
      <image-input
        name="logo_picture"
        :form="form"
        label="Logo"
        :required="false"
      />

      <ImageWithSettings :form="form" name="cover_picture" :label="isFocused ? 'Fundo' : 'Capa (~1500px)'" kind="cover" />
    </div>

    <toggle-switch-input
      name="no_branding"
      :form="form"
      class="mt-4"
      @update:model-value="onChangeNoBranding"
    >
      <template #label>
        <InputLabel
          :label="'Ocultar marca Forms4U'"
          :native-for="'no_branding'"
          class="text-sm font-medium!"
        />
        <PlanTag
          upgrade-modal-title="Upgrade today to remove Forms4U branding"
          class="-mt-1 ml-2"
        />
      </template>
    </toggle-switch-input>

    <EditorSectionHeader
      icon="heroicons:cog-6-tooth-16-solid"
      title="Opções avançadas"
    />

    <toggle-switch-input
      v-if="isFocused"
      name="settings.navigation_arrows"
      :form="form"
      class="mt-2"
      label="Mostrar setas de navegação"
    />
    <toggle-switch-input
      v-if="isFocused"
      name="settings.auto_next"
      :form="form"
      label="Avançar ao selecionar"
      help="Vai automaticamente para a próxima página ao selecionar uma opção (caixa de seleção, lista suspensa etc.)"
    />
    <toggle-switch-input
      name="show_progress_bar"
      :form="form"
      label="Mostrar barra de progresso"
      :help="
        form.show_progress_bar
          ? 'A barra fica no topo da página (acima da navegação neste editor) ou abaixo do título quando incorporado'
          : ''
      "
    />
    <toggle-switch-input
      name="transparent_background"
      :form="form"
      label="Fundo transparente"
      help="Quando o formulário estiver incorporado"
    />
    <toggle-switch-input
      name="confetti_on_submission"
      :form="form"
      label="Confete ao enviar com sucesso"
      @update:model-value="onChangeConfettiOnSubmission"
    />
    <ToggleSwitchInput
      name="auto_focus"
      :form="form"
      label="Focar automaticamente o primeiro campo da página"
    />
  </div>
</template>

<script setup>
import EditorSectionHeader from "./EditorSectionHeader.vue"
import { useWorkingFormStore } from "../../../../../stores/working_form"
import GoogleFontPicker from "../../../editors/GoogleFontPicker.vue"
import PlanTag from "~/components/app/PlanTag.vue"
import { DEFAULT_COLOR, ensureSettingsObject } from "@/composables/forms/initForm"
import PresentationStyleSwitch from "./PresentationStyleSwitch.vue"
import ImageWithSettings from "../media/ImageWithSettings.vue"


const workingFormStore = useWorkingFormStore()
const { openSubscriptionModal } = useAppModals()
const { hasFeature } = usePlanFeatures()
const form = storeToRefs(workingFormStore).content
const isMounted = ref(false)
const confetti = useConfetti()
const showGoogleFontPicker = ref(false)
const { $i18n } = useNuxtApp()

const isPro = computed(() => {
  return hasFeature('branding.removal')
})

const isFocused = computed(() => form.value?.presentation_style === 'focused')

const availableLocales = computed(() => {
  return $i18n.locales?.value.map(locale => ({ name: locale.name, value: locale.code })) ?? []
})

onMounted(() => {
  isMounted.value = true
  
  // Ensure settings is a plain, writable object (avoid writing into readonly proxies)
  ensureSettingsObject(form.value)
  
  // Set default value for navigation_arrows in focused mode if not defined
  if (isFocused.value && form.value.settings.navigation_arrows === undefined) {
    form.value.settings.navigation_arrows = true
  }

  // Set default value for auto_next in focused mode if not defined
  if (isFocused.value && form.value.settings.auto_next === undefined) {
    form.value.settings.auto_next = true
  }
})

const onChangeConfettiOnSubmission = (val) => {
  if (isMounted.value && val) {
    confetti.play()
  }
}

const onChangeNoBranding = (val) => {
  if (!isPro.value && val) {
    openSubscriptionModal({ modal_title: "Faça upgrade para remover a marca Forms4U" })
    setTimeout(() => {
      form.value.no_branding = false
    }, 300)
  } 
}

const onApplyFont = (val) => {
  form.value.font_family = val
  showGoogleFontPicker.value = false
}
</script>
