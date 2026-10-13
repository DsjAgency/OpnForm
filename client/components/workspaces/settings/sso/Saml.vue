<template>
  <div class="space-y-4">
    <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <div class="flex items-center gap-2">
          <h3 class="text-lg font-medium text-neutral-900">Configurações de SAML</h3>
          <UBadge color="neutral" variant="subtle">
            Ainda não disponível
          </UBadge>
        </div>
        <p class="mt-1 text-sm text-neutral-500">
          O login único via SAML ainda não está disponível. Use OIDC para o SSO do espaço de trabalho.
        </p>
      </div>
    </div>

    <!-- Empty State -->
    <div class="text-center py-12">
      <UIcon 
        name="i-heroicons-shield-check" 
        class="w-12 h-12 text-neutral-400 mx-auto mb-4" 
      />
      <h4 class="text-lg font-medium text-neutral-900 mb-2">
        Nenhuma conexão SAML ainda
      </h4>
      <p class="text-neutral-500 mb-6 max-w-md mx-auto">
        A configuração de SAML não existe nesta versão do Forms4U.
      </p>
    </div>
  </div>
</template>
