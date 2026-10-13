export const ATTRIBUTION_MAX_VALUE_LENGTH = 2048

export const ATTRIBUTION_PARAMETERS = Object.freeze([
  'utm_source',
  'utm_medium',
  'utm_campaign',
  'utm_id',
  'utm_term',
  'utm_content',
  'utm_source_platform',
  'utm_creative_format',
  'utm_marketing_tactic',
  'gclid',
  'gbraid',
  'wbraid',
  'dclid',
  'fbclid',
  'ttclid',
  'msclkid',
])

export const ATTRIBUTION_PARAMETER_LABELS = Object.freeze({
  utm_source: 'Origem',
  utm_medium: 'Mídia',
  utm_campaign: 'Campanha',
  utm_id: 'ID da campanha',
  utm_term: 'Termo de busca',
  utm_content: 'Conteúdo',
  utm_source_platform: 'Plataforma de origem',
  utm_creative_format: 'Formato do criativo',
  utm_marketing_tactic: 'Tática de marketing',
  gclid: 'Google Click ID',
  gbraid: 'Google GBRAID',
  wbraid: 'Google WBRAID',
  dclid: 'Google Display Click ID',
  fbclid: 'Meta Click ID',
  ttclid: 'TikTok Click ID',
  msclkid: 'Microsoft Ads Click ID',
})

export function attributionColumnId(parameter) {
  return `meta.attribution.${parameter}`
}

export function attributionColumnAccessor(parameter) {
  return row => row?.[attributionColumnId(parameter)]
}

export function attributionParameterFromColumnId(columnId) {
  const prefix = 'meta.attribution.'
  if (typeof columnId !== 'string' || !columnId.startsWith(prefix)) return null

  const parameter = columnId.slice(prefix.length)
  return ATTRIBUTION_PARAMETERS.includes(parameter) ? parameter : null
}

export function detectedAttributionParameters(rows) {
  if (!Array.isArray(rows)) return []

  return ATTRIBUTION_PARAMETERS.filter(parameter => rows.some((row) => {
    const value = row?.[attributionColumnId(parameter)]
    return value !== undefined && value !== null && value !== ''
  }))
}

export function isColumnVisibilityTransition(fromSectionType, toSectionType) {
  return ['visible', 'hidden'].includes(fromSectionType)
    && ['visible', 'hidden'].includes(toSectionType)
    && fromSectionType !== toSectionType
}

export function sanitizeAttribution(parameters) {
  if (!parameters || typeof parameters !== 'object' || Array.isArray(parameters)) return {}

  return ATTRIBUTION_PARAMETERS.reduce((attribution, parameter) => {
    const value = parameters[parameter]
    if (typeof value !== 'string' || value.trim() === '' || value.length > ATTRIBUTION_MAX_VALUE_LENGTH) {
      return attribution
    }

    attribution[parameter] = value
    return attribution
  }, {})
}

export function extractAttribution(searchParams) {
  let params
  try {
    params = searchParams instanceof URLSearchParams
      ? searchParams
      : new URLSearchParams(searchParams || '')
  } catch {
    return {}
  }

  return ATTRIBUTION_PARAMETERS.reduce((attribution, parameter) => {
    const value = params.getAll(parameter).find(candidate => (
      candidate.trim() !== '' && candidate.length <= ATTRIBUTION_MAX_VALUE_LENGTH
    ))

    if (value !== undefined) attribution[parameter] = value

    return attribution
  }, {})
}

export function mergeAttribution(iframeAttribution, parentAttribution) {
  return {
    ...sanitizeAttribution(parentAttribution),
    ...sanitizeAttribution(iframeAttribution),
  }
}
