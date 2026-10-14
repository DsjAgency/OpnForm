<template>
  <UModal
    v-model:open="isOpen"
    :ui="{ content: 'sm:max-w-2xl overflow-hidden' }"
    :dismissible="!loading"
  >
    <template #header>
      <div class="flex w-full items-start justify-between gap-4">
        <div class="flex min-w-0 items-start gap-3">
          <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-600">
            <Icon
              name="i-heroicons-arrow-down-tray"
              class="h-5 w-5"
            />
          </span>
          <div class="min-w-0">
            <h3 class="text-base font-semibold leading-6 text-neutral-950 dark:text-white">
              Importar formulário
            </h3>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
              Cole a URL de um formulário compatível e o Forms4U vai detectar o provedor.
            </p>
          </div>
        </div>
        <UButton
          color="neutral"
          variant="ghost"
          icon="i-heroicons-x-mark-20-solid"
          class="-mr-2 -mt-1"
          @click="isOpen = false"
        />
      </div>
    </template>

    <template #body>
      <form @submit.prevent="submitImport">
        <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-[0_20px_55px_-38px_rgba(15,23,42,0.55)]">
          <div class="flex items-center gap-3 border-b border-neutral-100 bg-neutral-50/80 px-3 py-2">
            <span
              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border text-sm shadow-sm"
              :class="activeSourceConfig ? activeSourceConfig.iconWrapClass : 'border-neutral-200 bg-white text-neutral-500'"
            >
              <Icon
                :name="activeSourceConfig?.icon || 'i-heroicons-link'"
                :class="activeSourceConfig?.iconClass || 'h-4 w-4'"
              />
            </span>
            <div class="min-w-0 flex-1">
              <input
                v-model="importForm.url"
                type="text"
                inputmode="url"
                name="url"
                autocomplete="url"
                aria-label="URL do formulário"
                :placeholder="activePlaceholder"
                :disabled="loading"
                class="w-full border-0 bg-transparent px-0 py-2 text-base font-medium text-neutral-950 placeholder:text-neutral-400 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-70"
                @input="handleUrlInput"
              >
            </div>
            <UButton
              type="submit"
              icon="i-heroicons-arrow-down-tray"
              :loading="loading"
              :disabled="!canSubmit"
              :color="canSubmit ? 'primary' : 'neutral'"
              :variant="canSubmit ? 'solid' : 'soft'"
              label="Importar"
              class="shrink-0"
            />
          </div>

          <div class="flex flex-col gap-3 px-3 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div
              class="flex min-w-0 items-start gap-2 text-sm"
              :class="statusClass"
            >
              <Icon
                :name="statusIcon"
                class="mt-0.5 h-4 w-4 shrink-0"
              />
              <span class="min-w-0">{{ statusMessage }}</span>
            </div>
            <button
              v-if="importForm.url"
              type="button"
              class="w-fit text-xs font-medium text-neutral-400 transition hover:text-neutral-700"
              @click="clearUrl"
            >
              Limpar
            </button>
          </div>
        </div>
      </form>

      <div class="mt-4">
        <div class="mb-2 flex items-center justify-between gap-3">
          <p class="text-xs font-medium uppercase text-neutral-400">
            Importações compatíveis
          </p>
          <UBadge
            v-if="detectedSourceConfig && !sourceIssue"
            color="primary"
            variant="soft"
            size="sm"
            :label="detectedSourceConfig.label + ' detectado'"
          />
        </div>

        <div
          class="grid grid-cols-1 gap-2"
          :class="supportedSources.length === 4 ? 'sm:grid-cols-4' : 'sm:grid-cols-3'"
        >
          <div
            v-for="source in supportedSources"
            :key="source.id"
            class="min-w-0 rounded-lg border bg-white p-3 shadow-sm transition duration-200"
            :class="sourceCardClass(source)"
          >
            <div class="mb-3 flex items-start justify-between gap-2">
              <span
                class="flex h-9 w-9 items-center justify-center rounded-lg border shadow-sm"
                :class="source.iconWrapClass"
              >
                <Icon
                  :name="source.icon"
                  :class="source.iconClass"
                />
              </span>
              <Icon
                v-if="detectedSource === source.id && !sourceIssue"
                name="i-heroicons-check-circle"
                class="h-4 w-4 text-primary-800"
              />
              <Icon
                v-else-if="source.requiresAuth && !authenticated"
                name="i-heroicons-lock-closed"
                class="h-4 w-4 text-neutral-300"
              />
            </div>
            <p class="text-sm font-semibold text-neutral-950">{{ source.label }}</p>
            <p
              class="mt-1 max-w-full truncate text-xs leading-5 text-neutral-500"
              :title="source.domain"
            >
              {{ source.domain }}
            </p>
          </div>
        </div>
      </div>

      <VTransition name="fade">
        <div
          v-if="isGoogleSource && !sourceIssue"
          class="mt-4 rounded-lg border border-neutral-200 bg-white p-4 shadow-sm"
        >
          <div
            v-if="!authenticated"
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
          >
            <div class="min-w-0">
              <p class="text-sm font-semibold text-neutral-950">O Google Forms precisa de uma conta</p>
              <p class="mt-1 text-sm text-neutral-500">
                Precisamos de acesso somente leitura ao seu Google Forms para importar esta URL.
              </p>
            </div>
            <UButton
              label="Entrar para importar"
              icon="i-heroicons-user-circle"
              class="justify-center"
              @click="appStore.quickRegisterModal = true"
            />
          </div>

          <div
            v-else-if="loadingProviders"
            class="py-4 text-center"
          >
            <Loader class="mx-auto mb-3 h-6 w-6" />
            <p class="text-sm text-neutral-500">Verificando a conexão com o Google...</p>
          </div>

          <div v-else-if="filteredProviders.length">
            <FlatSelectInput
              v-model="selectedProviderId"
              :form="importForm"
              name="oauth_provider_id"
              :options="filteredProviders"
              :disable-options="disabledProviderIds"
              disable-options-tooltip="Re-connect account to fix permissions"
              display-key="email"
              option-key="id"
              emit-key="id"
              :required="true"
              label="Conta Google"
            >
              <template #help>
                <InputHelp>
                  <span>
                    <a
                      class="cursor-pointer text-primary-700"
                      @click="connectProvider"
                    >
                      Conectar outra conta
                    </a>
                  </span>
                </InputHelp>
              </template>
            </FlatSelectInput>
          </div>

          <div
            v-else
            class="text-center"
          >
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg border border-primary-100 bg-primary-50 text-primary-800">
              <Icon
                name="i-simple-icons-google"
                class="h-5 w-5"
              />
            </div>
            <UButton
              :loading="connecting"
              label="Conectar Google"
              icon="i-simple-icons-google"
              @click="connectProvider"
            />
            <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-neutral-500">
              Precisamos de acesso somente leitura para importar seu Google Forms.
            </p>
          </div>
        </div>
      </VTransition>
    </template>
  </UModal>
</template>

<script setup>
import { formsApi } from '~/api/forms'
import { WindowMessageTypes, useWindowMessage } from '~/composables/useWindowMessage'
import { detectFormImportSource } from '~/lib/forms/detect-form-import-source'

const props = defineProps({
  show: { type: Boolean, required: true },
  defaultSource: { type: String, default: null },
})

const emit = defineEmits(['close', 'imported'])

const isOpen = computed({
  get: () => props.show,
  set: (val) => {
    if (!val) emit('close')
  },
})

const importForm = useForm({
  url: '',
  oauth_provider_id: null,
})

const loading = ref(false)
const importError = ref('')
const suggestedSource = ref(null)
const connecting = ref(false)
const selectedProviderId = ref(null)

const appStore = useAppStore()
const oAuth = useOAuth()
const { isAuthenticated: authenticated } = useIsAuthenticated()
const isGoogleImportConfigured = computed(() => {
  return !!useFeatureFlag('services.google.auth', false) && !useFeatureFlag('self_hosted', false)
})
const showGoogleImportSource = computed(() => isGoogleImportConfigured.value && authenticated.value)

const sourceConfigs = {
  typeform: {
    id: 'typeform',
    label: 'Typeform',
    domain: 'typeform.com/to/...',
    placeholder: 'https://example.typeform.com/to/abc123',
    icon: 'i-simple-icons-typeform',
    iconClass: 'h-4 w-4 text-[#262627]',
    iconWrapClass: 'border-neutral-200 bg-white text-neutral-950',
  },
  tally: {
    id: 'tally',
    label: 'Tally',
    domain: 'tally.so/r/...',
    placeholder: 'https://tally.so/r/mBGjOq',
    icon: 'opnform:tally',
    iconClass: 'h-4 w-4 text-[#725BFF]',
    iconWrapClass: 'border-violet-100 bg-violet-50 text-violet-600',
  },
  fillout: {
    id: 'fillout',
    label: 'Fillout',
    domain: 'fillout.com/t/...',
    placeholder: 'https://example.fillout.com/t/abc123',
    icon: 'i-simple-icons-fillout',
    iconClass: 'h-4 w-4 text-[#FFC738]',
    iconWrapClass: 'border-amber-100 bg-amber-50 text-amber-600',
  },
  zoho_forms: {
    id: 'zoho_forms',
    label: 'Zoho Forms',
    domain: '…/form/Nome/formperma/…',
    placeholder: 'https://forms.cidadania4u.com.br/cidadania4u/form/.../formperma/...',
    icon: 'i-simple-icons-zoho',
    iconClass: 'h-4 w-4 text-[#E42527]',
    iconWrapClass: 'border-red-100 bg-red-50 text-red-600',
  },
  google_forms: {
    id: 'google_forms',
    label: 'Google Forms',
    domain: 'docs.google.com/forms/d/...',
    placeholder: 'https://docs.google.com/forms/d/.../edit',
    icon: 'i-simple-icons-googleforms',
    iconClass: 'h-4 w-4 text-[#7248B9]',
    iconWrapClass: 'border-purple-100 bg-purple-50 text-purple-600',
    requiresAuth: true,
  },
}

const supportedSources = computed(() => Object.values(sourceConfigs).filter((source) => {
  return source.id !== 'google_forms' || showGoogleImportSource.value
}))
const supportedSourceConfigs = computed(() => {
  return Object.fromEntries(supportedSources.value.map(source => [source.id, source]))
})
const importDetection = computed(() => detectFormImportSource(importForm.url))
const detectedSource = computed(() => importDetection.value.source)
const isGoogleSource = computed(() => detectedSource.value === 'google_forms')
const detectedSourceConfig = computed(() => supportedSourceConfigs.value[detectedSource.value] ?? null)
const activeSourceConfig = computed(() => detectedSourceConfig.value ?? supportedSourceConfigs.value[suggestedSource.value] ?? null)
const supportedSourcesLabel = computed(() => formatSourceList(supportedSources.value.map(source => source.label)))
const activePlaceholder = computed(() => activeSourceConfig.value?.placeholder ?? `Cole uma URL do ${supportedSourcesLabel.value}`)
const sourceIssue = computed(() => {
  if (isGoogleSource.value && !isGoogleImportConfigured.value) {
    return 'google_unavailable'
  }

  return importDetection.value.reason
})
const shouldFetchGoogleProviders = computed(() => {
  return props.show && authenticated.value && isGoogleImportConfigured.value && isGoogleSource.value
})
const { data: providersData, isLoading: loadingProviders } = oAuth.providers({
  enabled: shouldFetchGoogleProviders,
})

const filteredProviders = computed(() =>
  (providersData.value || []).filter(provider => provider.provider === 'google')
)

const disabledProviderIds = computed(() => {
  return filteredProviders.value
    .filter(provider => !provider.scopes?.includes(oAuth.googleFormsPermissionScope))
    .map(provider => provider.id)
})

const validGoogleProviders = computed(() => {
  return filteredProviders.value.filter(provider => !disabledProviderIds.value.includes(provider.id))
})

const canSubmit = computed(() => {
  if (loading.value || !importForm.url || !detectedSource.value || sourceIssue.value) {
    return false
  }

  if (!isGoogleSource.value) {
    return true
  }

  return authenticated.value && !!selectedProviderId.value
})

const statusIcon = computed(() => {
  if (importError.value || sourceIssue.value || (importForm.url && !detectedSource.value)) {
    return 'i-heroicons-exclamation-circle'
  }

  if (detectedSource.value) {
    return 'i-heroicons-check-circle'
  }

  return 'i-heroicons-sparkles'
})

const statusClass = computed(() => {
  if (importError.value || sourceIssue.value || (importForm.url && !detectedSource.value)) {
    return 'text-red-600'
  }

  if (detectedSource.value) {
    return 'text-primary-800'
  }

  return 'text-neutral-500'
})

const statusMessage = computed(() => {
  if (importError.value) {
    return importError.value
  }

  if (!importForm.url) {
    return 'Cole uma URL e vamos detectar a origem automaticamente.'
  }

  if (sourceIssue.value) {
    return issueMessage(sourceIssue.value)
  }

  if (!detectedSource.value) {
    return 'Esta URL não é de uma origem compatível.'
  }

  if (isGoogleSource.value && !authenticated.value) {
    return 'Google Forms detectado. Entre para conectar o Google e importar este formulário.'
  }

  if (isGoogleSource.value && authenticated.value && !selectedProviderId.value) {
    return 'Google Forms detectado. Selecione uma conta Google antes de importar.'
  }

  return `${detectedSourceConfig.value.label} detectado. Pronto para importar.`
})

watch(() => props.show, (open) => {
  if (!open) {
    loading.value = false
    importError.value = ''
    connecting.value = false
    return
  }

  suggestedSource.value = props.defaultSource ?? null
  importForm.url = ''
  importForm.oauth_provider_id = null
  selectedProviderId.value = null
  importForm.errors.clear()
  importError.value = ''
})

watch(detectedSource, (source) => {
  importError.value = ''
  importForm.errors.clear()

  if (source !== 'google_forms') {
    importForm.oauth_provider_id = null
    selectedProviderId.value = null
  }
})

watch([validGoogleProviders, detectedSource], ([providers, source]) => {
  if (source !== 'google_forms' || selectedProviderId.value || providers.length !== 1) {
    return
  }

  selectedProviderId.value = providers[0].id
  importForm.oauth_provider_id = providers[0].id
})

watch(selectedProviderId, (providerId) => {
  importForm.oauth_provider_id = providerId
  importError.value = ''
})

const handleUrlInput = () => {
  importError.value = ''
  importForm.errors.clear()
}

const clearUrl = () => {
  importForm.url = ''
  importError.value = ''
  importForm.errors.clear()
}

const sourceCardClass = (source) => {
  if (detectedSource.value === source.id && !sourceIssue.value) {
    return 'border-primary-200 ring-1 ring-primary-100'
  }

  if (source.requiresAuth && !authenticated.value) {
    return 'border-neutral-200 opacity-70'
  }

  return 'border-neutral-200'
}

const submitImport = () => {
  if (loading.value) return

  if (!detectedSource.value) {
    importError.value = importForm.url ? 'Esta URL não é de uma origem compatível.' : 'A URL do formulário é obrigatória.'
    return
  }

  if (sourceIssue.value) {
    importError.value = issueMessage(sourceIssue.value)
    return
  }

  if (isGoogleSource.value && !authenticated.value) {
    appStore.quickRegisterModal = true
    return
  }

  if (isGoogleSource.value && !selectedProviderId.value) {
    importError.value = 'Selecione uma conta Google para importar este formulário.'
    return
  }

  loading.value = true
  importError.value = ''

  const importData = {
    url: importDetection.value.normalizedUrl,
  }

  if (isGoogleSource.value) {
    importData.oauth_provider_id = selectedProviderId.value
  }

  formsApi.import({
    source: detectedSource.value,
    import_data: importData,
  })
    .then((response) => {
      useAlert().success(response.message || 'Formulário importado!')
      emit('imported', response.form)
      emit('close')
    })
    .catch((error) => {
      const message = error?.data?.message || error?.message || 'Não foi possível importar o formulário. Confira a URL e tente novamente.'
      importError.value = message
      useAlert().error(message)
    })
    .finally(() => {
      loading.value = false
    })
}

const connectProvider = () => {
  connecting.value = true
  oAuth.connect('google', false, true, true, { intent: 'forms_import' })
    .catch(() => {
      connecting.value = false
    })
}

function issueMessage(reason) {
  if (reason === 'invalid_url') {
    return 'Digite uma URL válida, por exemplo https://tally.so/r/mBGjOq.'
  }

  if (reason === 'google_published_url' || reason === 'google_edit_url') {
    return 'Use a URL de edição do Google Forms: https://docs.google.com/forms/d/FORM_ID/edit.'
  }

  if (reason === 'google_unavailable') {
    return 'A importação do Google Forms não está disponível neste ambiente.'
  }

  if (reason === 'zoho_permalink') {
    return 'Use o link público do formulário Zoho (…/form/NomeDoFormulario/formperma/…).'
  }

  if (reason === 'typeform_form_id') {
    return 'Use uma URL pública do Typeform, como https://seunome.typeform.com/to/FORM_ID.'
  }

  return `Esta URL não é do ${supportedSourcesLabel.value}.`
}

function formatSourceList(sources) {
  if (sources.length <= 1) {
    return sources[0] || 'uma origem compatível'
  }

  if (sources.length === 2) {
    return `${sources[0]} ou ${sources[1]}`
  }

  return `${sources.slice(0, -1).join(', ')} ou ${sources.at(-1)}`
}

const windowMessage = useWindowMessage(WindowMessageTypes.OAUTH_PROVIDER_CONNECTED)
onMounted(() => {
  windowMessage.listen(() => {
    connecting.value = false
    oAuth.invalidateProviders()
  }, {
    useMessageChannel: false,
    acknowledge: false,
  })
})
</script>
