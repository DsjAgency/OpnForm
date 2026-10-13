/**
 * Internal Cidadania4U build: OpnForm marketing pages (landing, pricing,
 * comparisons, enterprise, public integrations catalog, legal pages...) are
 * not used. Send visitors to the dashboard when logged in, or to login.
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

const isMarketingRoute = (name) => {
  if (!name || typeof name !== 'string') return false
  return MARKETING_ROUTES.has(name) || name.startsWith('opnform-vs-')
}

export default defineNuxtRouteMiddleware((to) => {
  if (!isMarketingRoute(to.name)) return

  const { isAuthenticated } = useIsAuthenticated()
  return navigateTo({ name: isAuthenticated.value ? 'home' : 'login' }, { redirectCode: 302 })
})
