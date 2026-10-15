<template>
  <UDropdownMenu
    v-if="user"
    :items="dropdownItems"
    :ui="{
      content: 'w-64 overflow-x-hidden',
      viewport: 'overflow-x-hidden',
      label: 'w-full min-w-0 p-3',
      item: 'rounded-md'
    }"
    arrow
  >
    <slot :user="user" />
    
    <template #user-info>
      <div class="flex w-full min-w-0 items-center gap-3">
        <UserAvatar
          :src="user.photo_url"
          :name="user.name"
          size="size-10"
          text-size="text-sm"
          class="ring-2 ring-white shadow-sm"
        />
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-neutral-900 truncate">
            {{ user.name }}
          </p>
          <p class="text-xs font-normal text-neutral-500 truncate" :title="user.email">
            {{ user.email }}
          </p>
        </div>
      </div>
    </template>
  </UDropdownMenu>


</template>

<script setup>
import { computed } from "vue"

const { openUserSettings } = useAppModals()
const router = useRouter()

const { user: userQuery, logout: logoutMutationFactory } = useAuth()
const { data: user } = userQuery()

// Create logout mutation
const logoutMutation = logoutMutationFactory()

const logout = () => {
  // Logout mutation handles cache clearing and navigation automatically
  logoutMutation.mutateAsync()
}

const dropdownItems = computed(() => {
  if (!user.value) return []

  const items = []

  // User info header
  items.push([
    {
      slot: 'user-info',
      type: 'label'
    }
  ])

  // Navigation items
  const navItems = []

  // Settings
  navItems.push({
    label: 'Configurações',
    icon: 'i-heroicons-cog-6-tooth',
    onSelect: () => openUserSettings('account')
  })

  // Admin - only show for moderators
  if (user.value.moderator) {
    navItems.push({
      label: 'Administração',
      icon: 'i-heroicons-shield-check',
      onSelect: () => router.push({ name: 'admin' })
    })
  }

  if (navItems.length > 0) {
    items.push(navItems)
  }

  // Logout
  items.push([
    {
      label: 'Sair',
      icon: 'i-heroicons-arrow-right-start-on-rectangle',
      color: 'error',
      onSelect: logout
    }
  ])

  return items
})
</script> 