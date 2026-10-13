<template>
  <UPopover
    v-if="!field.multi_lines"
    arrow
    :content="{ side: 'left', align: 'center' }"
  >
    <UButton
      class="mt-4"
      block
      color="neutral"
      variant="outline"
      :trailing-icon="field.input_mask ? 'i-heroicons-check-circle' : ''"
      label="Máscara de entrada"
    />
    <template #content>
      <div class="p-4 w-72">
        <!-- Header with help icon -->
        <div class="flex items-center justify-between mb-3">
          <h4 class="font-medium text-sm">
            Configurações da máscara
          </h4>
        </div>

        <TextInput
          name="input_mask"
          :form="field"
          label="Padrão"
          placeholder="(999) 999-9999"
          @update:model-value="onInputMaskChange"
        >
          <template #help>
            <InputHelp>
              <div class="space-y-1">
                <div><code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">9</code> = dígito (0-9)</div>
                <div><code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">a</code> = letra (a-z)</div>
                <div><code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">*</code> = letra ou dígito</div>
                <div><code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">?</code> = torna o anterior opcional</div>
              </div>
            </InputHelp>
          </template>
        </TextInput>

        <!-- Placeholder character -->
        <OptionSelectorInput
          v-if="field.input_mask"
          v-model="field.slot_char"
          name="slot_char"
          class="mt-3"
          label="Placeholder"
          help="Aparece nas posições vazias"
          :options="placeholderOptions"
          :columns="5"
          seamless
        />

        <!-- Common patterns -->
        <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
          <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
            Modelos rápidos:
          </div>
          <div class="flex flex-wrap gap-1">
            <UButton
              v-for="template in commonPatterns"
              :key="template.pattern"
              size="xs"
              color="neutral"
              variant="soft"
              :label="template.label"
              @click="applyTemplate(template.pattern)"
            />
          </div>
        </div>

        <!-- Clear button -->
        <UButton
          v-if="field.input_mask"
          class="mt-3"
          block
          size="sm"
          color="neutral"
          variant="ghost"
          icon="i-heroicons-x-mark"
          label="Remover máscara"
          @click="clearMask"
        />
      </div>
    </template>
  </UPopover>
</template>

<script setup>
const props = defineProps({
  field: {
    type: Object,
    required: true
  }
})


// Allowed characters regex (matching backend validation)
const ALLOWED_MASK_CHARS = /^[9a*().\s\-?]*$/

const placeholderOptions = [
  { name: '_', label: '_' },
  { name: '#', label: '#' },
  { name: '•', label: '•' },
  { name: '○', label: '○' },
  { name: '▢', label: '▢' },
]

const commonPatterns = [
  { label: 'Telefone', pattern: '(99) 99999-9999' },
  { label: 'Data', pattern: '99/99/9999' },
  { label: 'CEP', pattern: '99999-999' },
  { label: 'CPF', pattern: '999.999.999-99' },
]

const onInputMaskChange = (val) => {
  if (typeof val !== 'string') return

  // Sanitize: only allow valid mask characters
  if (val && !ALLOWED_MASK_CHARS.test(val)) {
    const cleanedValue = val.replace(/[^9a*().\s\-?]/g, '')
    props.field.input_mask = cleanedValue
  }

  // Set default placeholder char when mask is first set
  if (val && !props.field.slot_char) {
    props.field.slot_char = '_'
  }
}

const applyTemplate = (pattern) => {
  props.field.input_mask = pattern
  if (!props.field.slot_char) {
    props.field.slot_char = '_'
  }
}

const clearMask = () => {
  props.field.input_mask = null
  props.field.slot_char = null
}
</script>
