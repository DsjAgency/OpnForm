<template>
  <UModal v-model:open="isOpen" @close="handleCancel">
    <template #header>
      <h3 class="text-lg font-semibold">{{ modalTitle }}</h3>
    </template>

    <template #body>
      <div class="flex justify-center">
        <VForm :form="form" size="sm" @submit.prevent="handleSave" class="w-full max-w-md space-y-6">
          <div class="space-y-4 rounded-xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-600">
            <p class="text-xs uppercase tracking-wide text-neutral-500">Geral</p>
            <TextInput
              name="name"
              :form="form"
              label="Nome da conexão"
              :required="true"
              placeholder="SSO da empresa"
            />

            <TextInput
              name="slug"
              :form="form"
              label="Slug"
              :required="true"
              placeholder="company-sso"
              help="Identificador em minúsculas, só com hífens, que aparece nas URLs de redirecionamento."
            />

            <TextInput
              name="domain"
              :form="form"
              label="Domínio de e-mail"
              :required="true"
              :placeholder="userEmailDomain || 'exemplo.com.br'"
            >
              <template #help>
                <div class="space-y-1">
                  <p v-if="userEmailDomain" class="text-xs text-neutral-500">
                    Deve corresponder ao seu domínio de e-mail ('{{ userEmailDomain }}' ou um subdomínio dele).
                  </p>
                  <p v-else class="text-xs text-neutral-500">
                    Deve corresponder ao seu domínio de e-mail (por exemplo, se seu e-mail é usuario@empresa.com.br, use 'empresa.com.br' ou um subdomínio dele).
                  </p>
                  <p class="text-xs text-neutral-500">
                    Provedores de e-mail comuns não são permitidos.
                    <button
                      type="button"
                      class="text-blue-600 hover:text-blue-700 underline"
                      @click="openSupportChat"
                    >
                      Fale com o suporte
                    </button>
                    se precisar de ajuda.
                  </p>
                </div>
              </template>
            </TextInput>
          </div>

          <div class="space-y-4 rounded-xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-600">
            <p class="text-xs uppercase tracking-wide text-neutral-500">Segurança</p>
            
            <!-- Redirect URI Display -->
            <div v-if="redirectUri" class="rounded-lg border border-blue-200 bg-blue-50 p-3">
              <p class="text-xs font-medium text-blue-900 mb-1">URI de redirecionamento</p>
              <p class="text-xs text-blue-700 mb-2">
                Copie esta URL e adicione-a às URIs de redirecionamento permitidas no seu provedor OIDC:
              </p>
              <CopyContent
                :content="redirectUri"
                label="Copiar"
              />
              <div v-if="isEditing" class="mt-3 border-t border-blue-200 pt-3">
                <p class="mb-2 text-xs text-blue-700">
                  Se a URL da instância mudou, atualize esta URL de retorno, atualize-a no provedor e salve a conexão.
                </p>
                <UButton
                  type="button"
                  size="xs"
                  color="primary"
                  variant="soft"
                  label="Usar a URL atual do app"
                  @click="useCurrentAppUrl"
                />
              </div>
            </div>

            <TextInput
              name="issuer"
              :form="form"
              label="URL do emissor"
              :required="true"
              placeholder="https://idp.example.com"
              help="O emissor publicado pelo seu provedor OIDC, geralmente disponível na configuração OpenID dele."
            />

            <TextInput
              name="client_id"
              :form="form"
              label="Client ID"
              :required="true"
              placeholder="your-client-id"
            />

            <TextInput
              name="client_secret"
              :form="form"
              :label="isEditing ? 'Client Secret (opcional)' : 'Client Secret'"
              :required="!isEditing"
              native-type="password"
              placeholder="your-client-secret"
              :help="isEditing ? 'Deixe em branco para manter o client secret atual.' : undefined"
            />

            <ToggleSwitchInput
              name="options.require_state"
              :form="form"
              label="Exigir parâmetro state"
              help="Recomendado: valida o parâmetro state no retorno. Desative só se o seu IdP não enviar state."
            />

            <ToggleSwitchInput
              name="enabled"
              class="mt-2"
              :form="form"
              label="Ativado"
            />
          </div>

          <Collapse v-model="showFieldMappings" class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-600">
            <template #title>
              <p class="text-xs uppercase tracking-wide text-neutral-500">Mapeamento de campos</p>
            </template>
            <div class="space-y-4 pt-2">
              <p class="text-xs text-neutral-500">
                Map IdP claim field names to Forms4U fields. Use this if your IdP uses different field names (e.g., "preferred_username" for email).
              </p>
              
              <div class="space-y-2">
                <TextInput
                  name="options.field_mappings.email"
                  :form="form"
                  label="Campo de e-mail"
                  placeholder="email"
                  help="Nome da claim do IdP para o e-mail (ex.: 'email', 'preferred_username', 'mail'). Deixe vazio para usar o padrão 'email'."
                  size="sm"
                />
                <TextInput
                  name="options.field_mappings.name"
                  :form="form"
                  label="Campo de nome"
                  placeholder="name"
                  help="Nome da claim do IdP para o nome completo (ex.: 'name', 'display_name'). Deixe vazio para usar o padrão 'name'."
                  size="sm"
                />
              </div>
            </div>
          </Collapse>

          <Collapse v-model="showRoleMapping" class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-600">
            <template #title>
              <p class="text-xs uppercase tracking-wide text-neutral-500">Mapeamento de papéis</p>
            </template>
            <div class="space-y-4 pt-2">
              <p class="text-xs text-neutral-500">
                Associe grupos do IdP a papéis no espaço de trabalho. Cada usuário recebe o papel de maior privilégio entre os seus grupos.
              </p>
              
              <div v-if="roleMappings.length === 0" class="text-center py-4 text-sm text-neutral-400">
                No role mappings configured. Users will be assigned the default "member" role.
              </div>
              
              <div v-else class="space-y-2">
                <div
                  v-for="(mapping, index) in roleMappings"
                  :key="index"
                  class="flex items-start gap-2 p-2 rounded-md border border-neutral-200 bg-white"
                >
                  <div class="flex-1 grid grid-cols-2 gap-2">
                    <TextInput
                      :name="`options.group_role_mappings.${index}.idp_group`"
                      :form="form"
                      label="Valor do grupo no token"
                      placeholder="group-id-or-name"
                      help="Valor exato (diferencia maiúsculas de minúsculas) da claim groups ou group do ID token."
                      :required="true"
                      size="sm"
                    />
                    <SelectInput
                      :name="`options.group_role_mappings.${index}.role`"
                      :form="form"
                      label="Papel"
                      :required="true"
                      size="sm"
                      :options="roleOptions"
                    />
                  </div>
                  <UButton
                    size="xs"
                    variant="ghost"
                    color="red"
                    icon="i-heroicons-trash"
                    square
                    class="mt-6"
                    @click="removeRoleMapping(index)"
                  />
                </div>
              </div>
              
              <div v-if="showRoleMapping" class="flex justify-end">
                <UButton
                  size="xs"
                  variant="ghost"
                  icon="i-heroicons-plus"
                  @click="addRoleMapping"
                >
                  Adicionar mapeamento
                </UButton>
              </div>
            </div>
          </Collapse>
        </VForm>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-between gap-2 w-full">
        <UButton variant="ghost" color="neutral" @click="handleCancel">Cancelar</UButton>
        <UButton type="submit" :loading="isBusy" :disabled="isBusy" @click="handleSave">
          {{ actionLabel }}
        </UButton>
      </div>
    </template>
  </UModal>
</template>

<script setup>
import Collapse from '@/components/app/Collapse.vue'
import CopyContent from '@/components/open/forms/components/CopyContent.vue'
import { appUrl } from '~/lib/utils.js'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  form: { type: Object, required: true },
  connection: { type: Object, default: null },
  isBusy: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'save', 'cancel'])

const isEditing = computed(() => !!props.connection)
const modalTitle = computed(() => (isEditing.value ? 'Editar conexão OIDC' : 'Adicionar conexão OIDC'))
const actionLabel = computed(() => (isEditing.value ? 'Atualizar' : 'Criar'))

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})

// Get user email domain
const { data: user } = useAuth().user()
const userEmailDomain = computed(() => {
  if (!user.value?.email) return null
  const email = user.value.email
  const parts = email.split('@')
  return parts.length === 2 ? parts[1] : null
})

// Pre-fill domain field with user's email domain when creating new connection
watch([isOpen, userEmailDomain], ([isOpenVal, domain]) => {
  if (isOpenVal && !isEditing.value && domain && !props.form.domain) {
    props.form.domain = domain
  }
})

// Open Crisp chat for support
const openSupportChat = () => {
  useCrisp().openChat()
}

const roleOptions = [
  { value: 'member', name: 'Membro' },
  { value: 'editor', name: 'Editor' },
  { value: 'admin', name: 'Administrador' },
  { value: 'owner', name: 'Proprietário' },
]

const roleMappings = computed({
  get: () => {
    return props.form.options?.group_role_mappings || []
  },
  set: (value) => {
    if (!props.form.options) {
      props.form.options = {}
    }
    props.form.options.group_role_mappings = value
  }
})

const hasFieldMappings = computed(() => {
  const mappings = props.form.options?.field_mappings || {}
  return !!(mappings.email || mappings.name)
})

const showFieldMappings = ref(hasFieldMappings.value)

const showRoleMapping = ref(roleMappings.value.length > 0)

const redirectUri = computed(() => {
  if (props.form.redirect_path) {
    return props.form.redirect_path
  }

  const slug = props.form.slug
  if (!slug) return null
  
  // Use appUrl helper to construct the redirect URI
  return appUrl(`/auth/${slug}/callback`)
})

const useCurrentAppUrl = () => {
  if (!props.form.slug) return
  props.form.redirect_path = appUrl(`/auth/${props.form.slug}/callback`)
}

watch(hasFieldMappings, (newVal) => {
  if (!showFieldMappings.value && newVal) {
    showFieldMappings.value = true
  }
})

watch(roleMappings, (newVal) => {
  if (!showRoleMapping.value && newVal.length > 0) {
    showRoleMapping.value = true
  }
}, { deep: true })

const addRoleMapping = () => {
  const current = roleMappings.value || []
  roleMappings.value = [...current, { idp_group: '', role: 'member' }]
  if (!showRoleMapping.value) {
    showRoleMapping.value = true
  }
}

const removeRoleMapping = (index) => {
  const current = roleMappings.value || []
  roleMappings.value = current.filter((_, i) => i !== index)
}

const handleSave = () => emit('save')
const handleCancel = () => {
  emit('cancel')
  emit('update:modelValue', false)
}
</script>
