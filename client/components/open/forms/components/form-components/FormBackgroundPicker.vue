<template>
  <div>
    <InputLabel label="Fundo da página" class="text-sm font-medium! mb-1" />
    <div class="grid grid-cols-4 gap-2">
      <button
        v-for="option in options"
        :key="option.id"
        type="button"
        class="group flex flex-col items-center gap-1 rounded-lg p-1 text-xs text-neutral-600 transition-colors hover:bg-neutral-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
        :aria-pressed="selected === option.id"
        @click="select(option.id)"
      >
        <span
          class="relative flex aspect-[4/3] w-full items-center justify-center overflow-hidden rounded-md border-2 bg-neutral-50 transition-colors"
          :class="selected === option.id ? 'border-primary-700' : 'border-neutral-200 group-hover:border-neutral-300'"
          :style="option.thumb ? { backgroundImage: `url(${option.thumb})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}"
        >
          <Icon
            v-if="option.icon"
            :name="option.icon"
            class="h-5 w-5 text-neutral-400"
          />
          <span
            v-if="selected === option.id"
            class="absolute right-0.5 top-0.5 flex size-4 items-center justify-center rounded-full bg-primary-700 text-white"
          >
            <Icon name="heroicons:check-16-solid" class="size-3" />
          </span>
        </span>
        <span class="leading-tight text-center">{{ option.label }}</span>
      </button>
    </div>

    <image-input
      v-if="selected === 'custom'"
      v-model="customImage"
      name="background_image"
      label="Sua imagem de fundo"
      help="Use uma imagem larga (ex.: 1920×1080). Ela cobre a tela atrás do formulário."
      class="mt-3"
    />

    <p v-if="!appliesToForm" class="mt-2 text-xs text-neutral-500">
      O fundo aparece em formulários com largura "Centralizado" (ou no estilo "Focado" sem imagem de capa).
    </p>
  </div>
</template>

<script setup>
import { FORM_BACKGROUNDS } from '~/lib/forms/form-background.js'
import { ensureSettingsObject } from '@/composables/forms/initForm'

const props = defineProps({
  form: { type: Object, required: true },
})

const options = [
  ...FORM_BACKGROUNDS.map(({ id, label, thumb }) => ({ id, label: label.replace('Forms4U ', '').replace(/^./, (c) => c.toUpperCase()), thumb })),
  { id: 'none', label: 'Sem fundo', icon: 'heroicons:no-symbol' },
  { id: 'custom', label: 'Minha foto', icon: 'heroicons:photo' },
]

const selected = computed(() => props.form.settings?.background || FORM_BACKGROUNDS[0].id)

const customImage = computed({
  get: () => props.form.settings?.background_image || null,
  set: (value) => {
    ensureSettingsObject(props.form)
    props.form.settings.background_image = value || null
  },
})

const appliesToForm = computed(() => {
  if (props.form.presentation_style === 'focused') {
    return !props.form.cover_picture
  }
  return props.form.width === 'centered'
})

function select(id) {
  ensureSettingsObject(props.form)
  props.form.settings.background = id
}
</script>
