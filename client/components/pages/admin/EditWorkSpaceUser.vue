<template>
  <UModal
    v-model:open="showEditUserModal"
    :ui="{ width: 'lg:max-w-lg' }"
    @close="$emit('close')"
    title="Editar papel do usuário"
  >
    <template #body>
      <UCard>
        <div class="px-4">
          <form
            @submit.prevent="updateUserRole"
          >
            <div>
              <FlatSelectInput
                v-model="userNewRole"
                name="newUserRole"
                :label="'Novo papel de '+props.user.name"
                :options="[
                  { name: 'Usuário', value: 'user' },
                  { name: 'Administrador', value: 'admin' },
                  { name: 'Somente leitura', value: 'readonly' },
                ]"
                option-key="value"
                display-key="name"
              />
            </div>

            <div class="w-full mt-6">
              <UButton
                :loading="updateMutation.isPending.value"
                class="my-3"
                block
                label="Atualizar"
              />
            </div>
          </form>
        </div>
      </UCard>
    </template>
  </UModal>
</template>

<script setup> 
const props = defineProps(['user', 'showEditUserModal'])
const emit = defineEmits(['close', 'fetchUsers'])

const { currentId } = useCurrentWorkspace()
const { updateUserRole: updateUserRoleMutation } = useWorkspaceUsers()

const userNewRole = ref("")

const updateMutation = updateUserRoleMutation(currentId)

watch(() => props.user, () => {
  userNewRole.value = props.user.pivot.role
})

const updateUserRole = () => {
  updateMutation.mutateAsync({
    userId: props.user.id,
    data: { role: userNewRole.value }
  }).then(() => {
    useAlert().success("Papel do usuário atualizado.")
    emit('close')
    // No need to emit 'fetchUsers' - the mutation handles cache updates automatically
  }).catch(() => {
    useAlert().error("Ocorreu um erro ao atualizar o papel do usuário")
  })
}
</script>
