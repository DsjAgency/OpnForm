<template>
  <div class="space-y-4">

    <UAlert
      :icon="alertConfig.icon"
      :color="alertConfig.color"
      variant="subtle"
      :title="alertConfig.title"
      :description="alertConfig.description"
      :actions="alertConfig.actions"
    />

    <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h3 class="text-lg font-medium text-neutral-900">Configurações de OIDC</h3>
        <p class="mt-1 text-sm text-neutral-500">
          Configure o login único (SSO) via OpenID Connect (OIDC) para o seu espaço de trabalho.
        </p>
      </div>

      <UButton
        v-if="canManageConnections && canAccessFeature"
        label="Adicionar conexão"
        icon="i-heroicons-plus"
        @click="openCreateModal"
      />
      <UButton
        v-else-if="canManageConnections && !canAccessFeature"
        label="Adicionar conexão"
        icon="i-heroicons-plus"
        @click="openUpgradeModal"
      />
    </div>

    <!-- Connections List -->
    <div v-if="connectionsData && connectionsData.length > 0" class="space-y-3">
      <p class="text-sm text-neutral-500 max-w-xl">
        Cada conexão pode ser ligada a um domínio de e-mail verificado, usado para direcionar os usuários ao
        espaço de trabalho certo quando fazem login. Gerencie vários clientes por aqui e ative ou desative cada um sem
        perder as configurações.
      </p>
      <div class="grid gap-3 sm:grid-cols-2">
        <OidcConnectionCard
        v-for="connection in connectionsData"
        :key="connection.id"
          :connection="connection"
          :can-edit="canManageConnections && canAccessFeature"
          @edit="editConnection"
          @delete="deleteConnection"
          />
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!isConnectionsLoading" class="text-center py-12">
      <UIcon 
        name="i-heroicons-key" 
        class="w-12 h-12 text-neutral-400 mx-auto mb-4" 
      />
      <h4 class="text-lg font-medium text-neutral-900 mb-2">
        Nenhuma conexão OIDC ainda
      </h4>
      <p class="text-neutral-500 mb-4">
        Configure sua primeira conexão OIDC para ativar o login único no espaço de trabalho.
      </p>
      <UButton
        v-if="canManageConnections && canAccessFeature"
        label="Adicionar primeira conexão"
        icon="i-heroicons-plus"
        @click="openCreateModal"
      />
      <UButton
        v-else-if="canManageConnections && !canAccessFeature"
        label="Adicionar primeira conexão"
        icon="i-heroicons-plus"
        @click="openUpgradeModal"
      />
    </div>

    <!-- Create/Edit Modal -->
    <OidcConnectionModal
      :model-value="showCreateModal"
      :connection="editingConnection"
            :form="connectionForm"
      :is-busy="connectionForm.busy"
      @update:model-value="showCreateModal = $event"
      @save="saveConnection"
      @cancel="cancelEdit"
    />
  </div>
</template>

<script setup>
import { useOidcConnections } from '~/composables/query/useOidcConnections'
import OidcConnectionCard from './OidcConnectionCard.vue'
import OidcConnectionModal from './OidcConnectionModal.vue'

const { current: workspace } = useCurrentWorkspace()
const alert = useAlert()
const { openSubscriptionModal } = useAppModals()
const { handleLicenseError } = useLicenseUpgradeModal()

const workspaceId = computed(() => workspace.value?.id)

const { hasFeature } = usePlanFeatures()
const canManageConnections = computed(() => !!workspace.value && workspace.value.is_admin)

// OIDC is available on self-hosted instances; cloud workspaces still require Enterprise.
const isSelfHosted = computed(() => useFeatureFlag('self_hosted'))
const billingEnabled = computed(() => useFeatureFlag('billing.enabled'))
const canAccessFeature = computed(() => {
  if (isSelfHosted.value) return true
  return billingEnabled.value && hasFeature('sso.oidc')
})

const { connections, create, update, remove } = useOidcConnections(workspaceId)

// Allow viewing connections without Enterprise (Enterprise only required for create/update/delete on cloud)
const { data: connectionsData, isLoading: isConnectionsLoading } = connections()

const alertConfig = computed(() => {
  if (!isSelfHosted.value && !canAccessFeature.value) {
    return {
      icon: 'i-heroicons-information-circle',
      color: 'info',
      title: 'Plano Enterprise necessário',
      description: 'O SSO via OIDC exige o plano Enterprise. Faça upgrade para configurar o login único no espaço de trabalho.',
      actions: [
        {
          label: 'Upgrade para Enterprise',
          onClick: openUpgradeModal
        }
      ]
    }
  }

  if (!isSelfHosted.value && canAccessFeature.value) {
    return {
      icon: 'i-heroicons-check-circle',
      color: 'success',
      title: 'SSO via OIDC ativado',
      description: 'Configure as conexões de login único via OpenID Connect do espaço de trabalho.',
      actions: []
    }
  }

  return {
    icon: 'i-heroicons-information-circle',
    color: 'info',
    title: 'SSO via OIDC',
    description: 'O OIDC está disponível em instâncias auto-hospedadas. Instâncias gratuitas ficam limitadas a 2 usuários; ative uma licença Enterprise para adicionar mais.',
    actions: []
  }
})

const openUpgradeModal = () => {
  openSubscriptionModal({
    plan: 'enterprise',
    modal_title: 'Faça upgrade para o Enterprise e use SSO via OIDC',
    modal_description: 'O SSO via OIDC é um recurso Enterprise. Faça upgrade para configurar o login único no espaço de trabalho.'
  })
}

const openSsoLicenseModal = (error) => {
  return handleLicenseError(error, {
    includeUnauthorized: true,
    title: 'Licença Enterprise necessária',
    description: 'Ative uma licença Enterprise auto-hospedada para ter mais de 2 usuários ou usar recursos avançados.'
  })
}

const showCreateModal = ref(false)
const editingConnection = ref(null)

const emptyConnectionData = () => ({
  name: '',
  slug: '',
  issuer: '',
  client_id: '',
  client_secret: '',
  domain: '',
  redirect_path: '',
  enabled: true,
  options: {
    require_state: true,
    field_mappings: {
      email: '',
      name: ''
    },
    group_role_mappings: []
  }
})

const connectionForm = useForm(emptyConnectionData())

// Create mutations following useWorkspaces.js pattern
const createMutation = create()
const deleteMutation = remove()

const openCreateModal = () => {
  editingConnection.value = null
  connectionForm.resetAndFill(emptyConnectionData())
  showCreateModal.value = true
}

const saveConnection = () => {
  if (editingConnection.value) {
    // Update existing connection
    const updateMutation = update(editingConnection.value.id)
    const keepExistingSecret = !connectionForm.client_secret?.trim()

    // The API intentionally preserves the existing secret when it is omitted.
    // Do not send the empty field shown in the edit form as a replacement.
    if (keepExistingSecret) {
      delete connectionForm.client_secret
    }

    connectionForm.mutate(updateMutation)
      .then(() => {
        alert.success('Conexão OIDC atualizada')
        cancelEdit()
      })
      .catch((error) => {
        // Form handles validation errors automatically
        if (openSsoLicenseModal(error)) return
        if (error.response?.status !== 422) {
          alert.error(error.response?._data?.message ?? 'Não foi possível atualizar a conexão')
        }
      })
      .finally(() => {
        if (keepExistingSecret) {
          connectionForm.client_secret = ''
        }
      })
  } else {
    // Create new connection
    connectionForm.mutate(createMutation)
      .then(() => {
        alert.success('Conexão OIDC criada')
        cancelEdit()
      })
      .catch((error) => {
        // Form handles validation errors automatically
        if (openSsoLicenseModal(error)) return
        if (error.response?.status !== 422) {
          alert.error(error.response?._data?.message ?? 'Não foi possível criar a conexão')
        }
      })
  }
}

const editConnection = (connection) => {
  editingConnection.value = connection
  connectionForm.resetAndFill({
    name: connection.name,
    slug: connection.slug,
    issuer: connection.issuer,
    client_id: connection.client_id,
    client_secret: '', // Don't pre-fill secret
    enabled: connection.enabled,
    domain: connection.domain ?? '',
    redirect_path: connection.redirect_url ?? '',
    options: {
      require_state: connection.options?.require_state ?? true,
      field_mappings: {
        email: connection.options?.field_mappings?.email ?? '',
        name: connection.options?.field_mappings?.name ?? ''
      },
      group_role_mappings: connection.options?.group_role_mappings ?? []
    }
  })
  showCreateModal.value = true
}

const deleteConnection = (connection) => {
  alert.confirm(
    `Tem certeza de que quer excluir "${connection.name}"?`,
    () => {
      deleteMutation.mutateAsync(connection.id)
        .then(() => {
          alert.success('Conexão OIDC excluída')
        })
        .catch((error) => {
          if (openSsoLicenseModal(error)) return
          alert.error(error.response?._data?.message ?? 'Não foi possível excluir a conexão')
        })
    }
  )
}

const cancelEdit = () => {
  editingConnection.value = null
  connectionForm.resetAndFill(emptyConnectionData())
  showCreateModal.value = false
}
</script>
