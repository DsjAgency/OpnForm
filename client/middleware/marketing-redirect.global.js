/**
 * Internal Cidadania4U build: OpnForm marketing pages (landing, pricing,
 * comparisons, enterprise, public integrations catalog, legal pages...) are
 * not used. Send visitors to the dashboard when logged in, or to login.
 * The OpnForm template gallery goes to our own "Modelos" page.
 */
const MARKETING_ROUTES = new Set([
  'index',
  'pricing',
  'enterprise',
  'industry',
  'ai-form-builder',
  'integrations',
  'integrations-slug',
  'mcp',
  'privacy-policy',
  'terms-conditions',
  'report-abuse',
  'self-hosted-license',
  'self-hosted-checkout-success',
  'self-hosted-checkout-canceled',
  'forms-create-guest',
])

// OpnForm template gallery pages: our templates live in the "Modelos" page
const TEMPLATE_GALLERY_ROUTES = new Set([
  'templates',
  'templates-types-slug',
  'templates-industries-slug',
])

const isMarketingRoute = (name) => {
  if (!name || typeof name !== 'string') return false
  return MARKETING_ROUTES.has(name) || name.startsWith('opnform-vs-')
}

export default defineNuxtRouteMiddleware((to) => {
  const isTemplateGallery = TEMPLATE_GALLERY_ROUTES.has(to.name)
  if (!isTemplateGallery && !isMarketingRoute(to.name)) return

  const { isAuthenticated } = useIsAuthenticated()
  if (!isAuthenticated.value) {
    return navigateTo({ name: 'login' }, { redirectCode: 302 })
  }
  return navigateTo({ name: isTemplateGallery ? 'templates-my-templates' : 'home' }, { redirectCode: 302 })
})
