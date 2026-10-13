<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
      <div>
        <div class="flex items-center gap-2">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
            <Icon name="i-heroicons-cpu-chip" class="h-5 w-5" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-neutral-900">
              MCP e agentes de IA
            </h3>
            <p class="text-sm text-neutral-500">
              {{ pageDescription }}
            </p>
          </div>
        </div>
      </div>

      <UButton
        to="https://docs.opnform.com/integrations/mcp"
        target="_blank"
        label="Guia de configuração"
        icon="i-heroicons-arrow-up-right"
        variant="outline"
        color="neutral"
      />
    </div>

    <div v-if="isLoading" class="space-y-3">
      <USkeleton class="h-28 w-full" />
      <USkeleton class="h-40 w-full" />
    </div>

    <section
      v-else-if="loadError"
      class="rounded-xl border border-red-200 bg-red-50 p-5"
      data-testid="mcp-load-error"
    >
      <div class="flex items-start gap-3">
        <Icon name="i-heroicons-exclamation-circle" class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
        <div>
          <h4 class="font-semibold text-red-900">Não foi possível carregar as configurações de MCP</h4>
          <p class="mt-1 text-sm text-red-800">Confira a conexão com a API e tente novamente.</p>
          <UButton class="mt-3" label="Tentar novamente" color="error" variant="soft" @click="loadSettings" />
        </div>
      </div>
    </section>

    <template v-else-if="settings">
      <section class="overflow-hidden rounded-xl border border-neutral-200 bg-white">
        <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-start sm:justify-between">
          <div class="flex min-w-0 items-start gap-3">
            <div
              class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
              :class="statusIconClasses"
            >
              <Icon :name="statusIcon" class="h-5 w-5" />
            </div>
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <p class="font-semibold text-neutral-900">
                  {{ statusTitle }}
                </p>
                <UBadge :color="statusBadgeColor" variant="subtle">
                  {{ statusBadgeLabel }}
                </UBadge>
              </div>
              <p class="mt-1 max-w-xl text-sm leading-6 text-neutral-600">
                {{ statusDescription }}
              </p>
            </div>
          </div>

          <div
            v-if="isSelfHosted"
            class="flex shrink-0 items-center gap-3 rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2"
          >
            <span class="text-sm font-medium text-neutral-700">Ativar MCP</span>
            <USwitch
              :model-value="settings.enabled"
              :disabled="isSaving || (!settings.ready && !settings.enabled)"
              aria-label="Ativar MCP"
              data-testid="mcp-enabled-switch"
              @update:model-value="updateEnabled"
            />
          </div>
        </div>

      </section>

      <section
        v-if="!settings.ready"
        class="rounded-xl border border-red-200 bg-red-50 p-5"
        data-testid="mcp-readiness-blockers"
      >
        <div class="flex items-start gap-3">
          <Icon name="i-heroicons-exclamation-triangle" class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
          <div>
            <h4 class="font-semibold text-red-900">
              Conclua primeiro a configuração do servidor
            </h4>
            <p class="mt-1 text-sm leading-6 text-red-800">
              O MCP só pode ser ativado quando o OAuth estiver pronto nesta instância.
            </p>
            <ul class="mt-3 space-y-2 text-sm text-red-800">
              <li v-for="blocker in settings.blockers" :key="blocker.code" class="flex gap-2">
                <span aria-hidden="true">•</span>
                <span>{{ blocker.message }}</span>
              </li>
            </ul>
          </div>
        </div>
      </section>

      <section
        v-if="isMcpAvailable"
        class="space-y-4 rounded-xl border border-neutral-200 bg-white p-5"
        data-testid="mcp-connection-settings"
      >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">
              Endpoint de conexão
            </p>
            <h4 class="mt-1 font-semibold text-neutral-900">
              Conecte seu assistente de IA
            </h4>
            <p class="mt-1 text-sm leading-6 text-neutral-600">
              Use a URL do servidor direto no ChatGPT ou copie a configuração para o seu cliente de agente.
            </p>
          </div>
          <UButton
            label="Copiar link das configurações"
            icon="i-heroicons-link"
            variant="soft"
            color="neutral"
            @click="copyValue(sharedSettingsUrl, 'Settings link copied')"
          />
        </div>

        <div>
          <p class="mb-2 text-sm font-medium text-neutral-700">URL do servidor MCP</p>
          <CopyContent :content="settings.server_url" label="Copiar URL" />
        </div>

      </section>

      <section v-if="isMcpAvailable" data-testid="mcp-snippet-settings">
        <McpCodeSnippet
          :content="activeSnippet.content"
          :label="activeSnippet.label"
          @copy="copyValue(activeSnippet.content, `${activeSnippet.label} configuration copied`)"
        >
          <template #selector>
            <div class="flex min-w-0 items-center gap-1 overflow-x-auto" role="group" aria-label="Configuração do cliente MCP">
              <button
                v-for="snippet in snippetOptions"
                :key="snippet.key"
                type="button"
                :aria-pressed="activeSnippetKey === snippet.key"
                :data-testid="`mcp-snippet-${snippet.key}`"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs font-medium transition-colors"
                :class="activeSnippetKey === snippet.key
                  ? 'bg-white text-neutral-950 shadow-sm'
                  : 'text-neutral-400 hover:bg-white/10 hover:text-white'"
                @click="selectSnippet(snippet.key)"
              >
                <Icon :name="snippet.icon" class="h-3.5 w-3.5" />
                {{ snippet.label }}
              </button>
            </div>
          </template>
          <template #instructions>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <p class="text-sm leading-6 text-neutral-300">
                {{ activeSnippet.description }}
              </p>
              <UButton
                v-if="activeSnippet.installUrl"
                :label="activeSnippet.installLabel"
                :to="activeSnippet.installUrl"
                icon="i-heroicons-arrow-top-right-on-square"
                color="neutral"
                variant="solid"
                class="shrink-0"
              />
            </div>
          </template>
        </McpCodeSnippet>

        <details class="mt-3 overflow-hidden rounded-xl border border-neutral-200 bg-white" data-testid="mcp-portable-config">
          <summary class="cursor-pointer px-4 py-3 text-sm font-medium text-neutral-700">
            Configuração portátil do Agent Plugin
          </summary>
          <div class="border-t border-neutral-200 bg-neutral-950">
            <div class="flex justify-end border-b border-white/10 p-2">
              <UButton
                label="Copiar"
                icon="i-heroicons-clipboard-document"
                color="neutral"
                variant="solid"
                size="xs"
                @click.prevent="copyValue(settings.snippets.portable, 'Agent Plugin configuration copied')"
              />
            </div>
            <pre class="max-h-72 overflow-auto p-4 text-xs leading-6 text-blue-100"><code>{{ settings.snippets.portable }}</code></pre>
          </div>
        </details>
      </section>
    </template>
  </div>
</template>

<script setup>
import { mcpApi } from '~/api'
import CopyContent from '~/components/open/forms/components/CopyContent.vue'
import McpCodeSnippet from '~/components/users/settings/mcp/McpCodeSnippet.vue'

const alert = useAlert()
const { copy } = useClipboard()
const route = useRoute()
const router = useRouter()
const settings = ref(null)
const isLoading = ref(true)
const isSaving = ref(false)
const loadError = ref(false)

const snippetOptions = [
  {
    key: 'cursor',
    label: 'Cursor',
    icon: 'i-heroicons-cursor-arrow-rays',
    description: 'Instale o OpnForm direto no Cursor ou copie a configuração JSON abaixo.',
    installLabel: 'Instalar no Cursor',
  },
  {
    key: 'claude_code',
    label: 'Claude Code',
    icon: 'i-heroicons-chat-bubble-left-right',
    description: 'Rode este comando e depois use /mcp no Claude Code para autenticar no OpnForm.',
  },
  {
    key: 'chatgpt',
    label: 'ChatGPT',
    icon: 'i-heroicons-sparkles',
    description: 'Num plano que aceite apps MCP personalizados, crie um app no modo desenvolvedor do ChatGPT com esta URL de servidor e OAuth.',
  },
  {
    key: 'codex',
    label: 'Codex',
    icon: 'i-heroicons-command-line',
    description: 'Adicione este servidor MCP ao Codex. O OAuth começa quando você usa uma ferramenta ligada à conta.',
  },
  {
    key: 'other',
    label: 'Outro',
    icon: 'i-heroicons-ellipsis-horizontal',
    description: 'Use esta configuração HTTP (streamable) em outro cliente MCP compatível com OAuth.',
  },
]

const activeSnippetKey = ref(resolveSnippetKey(route.query.agent))

const activeSnippet = computed(() => {
  const option = snippetOptions.find(snippet => snippet.key === activeSnippetKey.value) || snippetOptions[0]

  return {
    ...option,
    content: settings.value?.snippets?.[option.key] || '',
    installUrl: settings.value?.install_urls?.[option.key] || null,
  }
})

const sharedSettingsUrl = computed(() => {
  if (!settings.value?.settings_url) return ''

  const url = new URL(settings.value.settings_url)
  url.searchParams.set('agent', activeSnippetKey.value)

  return url.toString()
})

const isMcpAvailable = computed(() => settings.value?.available === true)
const isSelfHosted = computed(() => settings.value?.self_hosted !== false)

const pageDescription = computed(() => {
  return isSelfHosted.value
    ? 'Conecte assistentes de IA diretamente a esta instância do OpnForm.'
    : 'Conecte assistentes de IA ao OpnForm Cloud.'
})

const statusTitle = computed(() => {
  if (!isSelfHosted.value) return 'O MCP do OpnForm está pronto'
  if (!settings.value?.ready) return 'Configuração do servidor necessária'
  return isMcpAvailable.value ? 'MCP disponível' : 'Pronto para conectar'
})

const statusDescription = computed(() => {
  if (!isSelfHosted.value) return 'Crie rascunhos privados sem login ou conecte sua conta para gerenciar formulários e ler respostas.'
  if (!settings.value?.ready) return 'Resolva os pré-requisitos abaixo antes de expor o endpoint MCP.'
  if (isMcpAvailable.value) return 'Assistentes de IA conectados podem criar e gerenciar formulários e ler respostas usando sua conta do OpnForm.'
  return 'O OAuth está pronto. Ative o MCP quando quiser que esta instância aceite conexões de agentes.'
})

const statusIcon = computed(() => {
  if (!isSelfHosted.value) return 'i-heroicons-check'
  if (!settings.value?.ready) return 'i-heroicons-exclamation-triangle'
  return settings.value?.enabled ? 'i-heroicons-check' : 'i-heroicons-pause'
})

const statusIconClasses = computed(() => {
  if (!isSelfHosted.value) return 'bg-emerald-100 text-emerald-600'
  if (!settings.value?.ready) return 'bg-red-100 text-red-600'
  return settings.value?.enabled ? 'bg-emerald-100 text-emerald-600' : 'bg-neutral-100 text-neutral-500'
})

const statusBadgeLabel = computed(() => {
  if (!isSelfHosted.value) return 'Cloud'
  if (!settings.value?.ready && settings.value?.enabled) return 'Indisponível'
  return settings.value?.enabled ? 'Ativado' : 'Desativado'
})

const statusBadgeColor = computed(() => {
  if (!isSelfHosted.value) return 'success'
  if (!settings.value?.ready && settings.value?.enabled) return 'error'
  return settings.value?.enabled ? 'success' : 'neutral'
})

function resolveSnippetKey(value) {
  return snippetOptions.some(snippet => snippet.key === value) ? value : 'codex'
}

function selectSnippet(key) {
  activeSnippetKey.value = resolveSnippetKey(key)
  router.replace({
    query: {
      ...route.query,
      agent: activeSnippetKey.value,
    },
  }).catch(() => {})
}

function loadSettings() {
  isLoading.value = true
  loadError.value = false
  mcpApi.status()
    .then((response) => {
      settings.value = response
    })
    .catch((error) => {
      loadError.value = true
      alert.error(error?.data?.message || 'Não foi possível carregar as configurações de MCP.')
    })
    .finally(() => {
      isLoading.value = false
    })
}

function updateEnabled(enabled) {
  isSaving.value = true
  mcpApi.update(enabled)
    .then((response) => {
      settings.value = response
      alert.success(enabled ? 'MCP ativado nesta instância.' : 'MCP desativado nesta instância.')
    })
    .catch((error) => {
      if (error?.data?.blockers) {
        settings.value = {
          ...settings.value,
          ready: false,
          available: false,
          blockers: error.data.blockers,
        }
      }
      alert.error(error?.data?.message || 'Não foi possível atualizar as configurações de MCP.')
    })
    .finally(() => {
      isSaving.value = false
    })
}

function copyValue(value, message) {
  copy(value)
  alert.success(message)
}

onMounted(loadSettings)

watch(() => route.query.agent, (agent) => {
  activeSnippetKey.value = resolveSnippetKey(agent)
})
</script>
