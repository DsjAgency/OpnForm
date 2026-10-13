<template>
  <AppProvider>
    <div
      id="app"
      class="bg-white dark:bg-notion-dark"
    >
      <NuxtLoadingIndicator color="#2563eb" />
      <NuxtLayout>
        <NuxtPage />
      </NuxtLayout>

      <!-- Third-party services and modals - only load when not on public form pages -->
      <ClientOnly v-if="!isPublicFormPage">
        <div
          class="fixed z-[9999] left-0 bottom-0 p-4" id="admin-actions"
        >
          <UButtonGroup size="sm">
            <LazyToolsStopImpersonation />
          </UButtonGroup>
        </div>

        <LazyVendorClarity />
        <LazyVendorFeatureBase />
        <LazyQuickRegister />
      </ClientOnly>
    </div>
  </AppProvider>
</template>

<script setup>
const config = useRuntimeConfig()
const route = useRoute()

// Check if current page is a public form page (for performance optimization)
const isPublicFormPage = computed(() => route.name === 'forms-slug')

// SEO and head configuration
useOpnSeoMeta({
  title: "Formulários",
  description:
    "Forms4U — formulários da Cidadania4U.",
  ogImage: "/img/social-preview.png",
  robots: () => {
    return config.public.env === "production" ? null : "noindex, nofollow"
  },
})

useHead({
  titleTemplate: (titleChunk) => {
    return titleChunk ? `${titleChunk} - Forms4U` : "Forms4U"
  },
  meta: [
    {
      name: 'mobile-web-app-capable',
      content: 'yes'
    },
    {
      name: 'apple-mobile-web-app-status-bar-style',
      content: 'black-translucent'
    },
  ],
  link: [
    {
      rel: 'icon',
      type: 'image/x-icon',
      href: '/favicon.ico'
    },
    {
      rel: 'apple-touch-icon',
      type: 'image/png',
      href: '/apple-touch-icon.png'
    }
  ],
  htmlAttrs: () => ({
    dir: 'ltr',
    lang: 'pt-BR'
  })
})
</script>
