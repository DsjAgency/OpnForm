// Global installation configuration: never forward cookies, tokens or custom domains.
const getFeatureFlags = defineCachedFunction(() => {
  const config = useRuntimeConfig()

  return $fetch('/content/feature-flags', {
    baseURL: config.privateApiBase || config.public.apiBase,
    retry: 0,
    headers: {
      accept: 'application/json',
      ...(config.apiSecret && { 'x-api-secret': config.apiSecret })
    }
  }).then((flags) => {
    if (!flags || typeof flags.self_hosted !== 'boolean') {
      throw createError({ statusCode: 502, statusMessage: 'Invalid feature flags response' })
    }
    return flags
  })
}, {
  maxAge: 10 * 60,
  name: 'feature-flags',
  // Arbitrary public query parameters must not create a new upstream fetch.
  getKey: () => 'global',
  swr: true,
  // Do not cache while setup is pending: once the first admin account is created,
  // the next request must see setup_required=false instead of a stale redirect to /setup.
  validate: (entry) => entry.value !== undefined && entry.value.setup_required !== true
})

export default defineEventHandler(() => getFeatureFlags())
