<template>
  <div class="space-y-4">
    <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h3 class="text-lg font-medium text-neutral-900">Informações do espaço de trabalho</h3>
        <p class="mt-1 text-sm text-neutral-500">
          Atualize as informações do seu espaço de trabalho.
        </p>
      </div>
    </div>

    <VForm @submit.prevent="updateProfile" size="sm">
   
        <div class="max-w-sm">
          <TextInput
            :disabled="workspace.is_readonly"
            :form="workspaceForm"
            name="name"
            label="Nome do espaço de trabalho"
            placeholder="Meu espaço de trabalho"
            :required="true"
          />
          <TextInput
            :disabled="workspace.is_readonly"
            :form="workspaceForm"
            name="emoji"
            label="Emoji (opcional)"
            placeholder="Emoji"
            help="Escolha um emoji para representar seu espaço de trabalho"
          />
        </div>

        <div class="mt-4">
          <UButton
            :disabled="workspace.is_readonly"
            type="submit"
            :loading="workspaceForm.busy"
            color="primary"
          >
            Salvar alterações
          </UButton>
        </div>
    </VForm>

    <div class="pt-8 border-t border-neutral-200">
      <div 
        v-if="workspace.is_admin" 
        class="space-y-2"
      >
        <h4 class="text-red-800 font-medium">Excluir espaço de trabalho</h4>
        <p class="text-neutral-500 text-sm">
          Isso exclui permanentemente todo o espaço de trabalho. Todos os formulários criados nele serão removidos. Esta ação não pode ser desfeita.
        </p>
        <UButton
          color="error"
          :loading="removeMutation.isPending.value"
          @click="confirmDeleteWorkspace"
        >
          Excluir espaço de trabalho
        </UButton>
      </div>

      <div 
        v-else
        class="space-y-2"
      >
        <h4 class="text-neutral-900 font-medium">Sair do espaço de trabalho</h4>
        <p class="text-neutral-500 text-sm">
          Você será removido do espaço de trabalho e perderá o acesso a todos os formulários dele.
        </p>
        <UButton
          color="error"
          :loading="leaveMutation.isPending.value"
          @click="leaveWorkSpace"
        >
          Sair do espaço de trabalho
        </UButton>
      </div>
    </div>
  </div>
</template>

<script setup>
const { update, remove, leave } = useWorkspaces()

const alert = useAlert()
const { closeWorkspaceSettings } = useAppModals()
const router = useRouter()

const { current: workspace } = useCurrentWorkspace()

const updateMutation = update(workspace.value.id)
const removeMutation = remove()
const leaveMutation = leave()

// Workspace form
const workspaceForm = useForm({
  name: '',
  emoji: ''
})

// Update profile
const updateProfile = () => {
  workspaceForm.mutate(updateMutation).then(() => {
    useAlert().success('Informações do espaço de trabalho atualizadas!')
  }).catch((error) => {
      console.error('Error updating workspace:', error)
  })
}

// Delete workspace confirmation
const confirmDeleteWorkspace = () => {
  alert.confirm(
    'Quer mesmo excluir seu espaço de trabalho?',
    deleteWorkspace
  )
}

// Delete workspace
const deleteWorkspace = () => {
  removeMutation.mutateAsync(workspace.value.id).then((data) => {
      alert.success(data.message)
      closeWorkspaceSettings()
      nextTick(() => {
        router.push({ name: "home", query: {} })
      })
  }).catch((error) => {
      alert.error(error.data?.message || 'Erro ao excluir o espaço de trabalho')
    })
}

// Leave workspace
const leaveWorkSpace = () => {
  alert.confirm(
    "Quer mesmo sair deste espaço de trabalho? Você perderá o acesso a todos os formulários dele.",
    () => {
      leaveMutation.mutateAsync(workspace.value.id).then(() => {
        alert.success("Você saiu do espaço de trabalho.")
        closeWorkspaceSettings()
        nextTick(() => {
          router.push({ name: "home", query: {} })
        })
      }).catch((error) => {
        console.error('Error leaving workspace:', error)
        alert.error("Ocorreu um erro ao sair do espaço de trabalho.")
      })
    },
  )
}


// Watch for user changes
watch(workspace, (newWorkspace) => {
  if (newWorkspace) {
    workspaceForm.fill({
      name: newWorkspace.name || '',
      emoji: newWorkspace.icon || ''
    })
  }
}, { immediate: true })
</script> 