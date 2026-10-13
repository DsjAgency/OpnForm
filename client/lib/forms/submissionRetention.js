export const SUBMISSION_RETENTION_UNITS = [
  { name: 'Dias', value: 'day' },
  { name: 'Semanas', value: 'week' },
  { name: 'Meses', value: 'month' },
  { name: 'Anos', value: 'year' }
]

export const SUBMISSION_RETENTION_SHORTCUTS = [
  { label: '1 semana', value: 1, unit: 'week' },
  { label: '1 mês', value: 1, unit: 'month' },
  { label: '6 meses', value: 6, unit: 'month' },
  { label: '1 ano', value: 1, unit: 'year' }
]

export function setSubmissionRetentionEnabled(form, enabled) {
  if (enabled) {
    return
  }

  form.submission_retention_value = null
  form.submission_retention_unit = null
}

export function syncSubmissionRetentionEnabled(currentState, value, unit) {
  if (value && unit) {
    return true
  }

  if (value == null && unit == null) {
    return false
  }

  return currentState
}

export function applySubmissionRetentionShortcut(form, shortcut) {
  form.submission_retention_value = shortcut.value
  form.submission_retention_unit = shortcut.unit
}
