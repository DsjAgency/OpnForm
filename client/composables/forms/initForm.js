import clonedeep from 'clone-deep'
import { isReadonly } from 'vue'
import { generateUUID } from "../../lib/utils.js"
export const DEFAULT_COLOR = '#640e2c'

export const initForm = (defaultValue = {}, withDefaultProperties = false) => {
  return useForm({
    title: "Formulário de contato",
    visibility: "public",
    workspace_id: null,
    properties: withDefaultProperties ? getDefaultProperties() : [],

    // Customization
    presentation_style: 'classic',
    language: 'pt',
    font_family: null,
    theme: "default",
    width: "centered",
    layout_rtl: false,
    dark_mode: "auto",
    color: DEFAULT_COLOR,
    no_branding: false,
    uppercase_labels: false,
    transparent_background: false,
    closes_at: null,
    closed_text:
      "Este formulário foi encerrado pelo responsável e não aceita mais respostas.",
    auto_save: true,
    auto_focus: true,
    border_radius: 'small',
    size: 'md',

    // Submission
    submit_button_text: null,
    re_fillable: false,
    re_fill_button_text: null,
    submitted_text:
      "Pronto, recebemos suas respostas. Obrigado pelo seu tempo e tenha um ótimo dia!",
    use_captcha: false,
    captcha_provider: 'recaptcha',
    max_submissions_count: null,
    max_submissions_reached_text:
      "Este formulário atingiu o número máximo de respostas e foi encerrado.",
    submission_retention_value: null,
    submission_retention_unit: null,
    editable_submissions_button_text: "Editar resposta",
    confetti_on_submission: false,

    // Security & Privacy
    can_be_indexed: true,

    // Custom SEO
    seo_meta: {},
    
    // Settings for various features
    settings: {},

    ...defaultValue,
  })
}

function getDefaultProperties() {
  return [
    {
      type: "nf-text",
      content: "<h1>Formulário de contato</h1><p>Preencha este formulário para falar com a gente.</p>",
      name: "Título",
      id: generateUUID(),
    },
    {
      name: "Nome",
      type: "text",
      hidden: false,
      required: true,
      placeholder: "Digite seu nome completo",
      id: generateUUID(),
    },
    {
      name: "E-mail",
      type: "email",
      hidden: false,
      placeholder: "Digite seu e-mail",
      id: generateUUID(),
    },
    {
      name: "Mensagem",
      type: "text",
      hidden: false,
      multi_lines: true,
      placeholder: "Digite sua mensagem",
      id: generateUUID(),
    },
  ]
}

/**
 * Sets default values for form properties if they are not already defined.
 * This function ensures that all necessary form fields have a valid initial value,
 * which helps maintain consistency and prevents errors due to undefined properties.
 * 
 * @param {Object} formData - The initial form data object
 * @returns {Object} A new object with default values applied where necessary
 */
export function setFormDefaults(formData) {
  const defaultValues = {
    title: 'Formulário sem título',
    visibility: 'public',
    theme: 'default',
    width: 'centered',
    size: 'md',
    border_radius: 'small',
    dark_mode: 'light',
    color: DEFAULT_COLOR,
    uppercase_labels: false,
    no_branding: false,
    transparent_background: false,
    submit_button_text: null,
    confetti_on_submission: false,
    show_progress_bar: false,
    bypass_success_page: false,
    can_be_indexed: true,
    use_captcha: false,
    captcha_provider: 'recaptcha',
    properties: [],
  }

  const filledFormData = clonedeep(formData)

  for (const [key, value] of Object.entries(defaultValues)) {
    if (filledFormData[key] === undefined || filledFormData[key] === null || (typeof value === 'string' && filledFormData[key] === '')) {
      filledFormData[key] = value
    }
  }

  // Entries that are not objects cannot represent form blocks. Discarding
  // them prevents legacy malformed data from breaking the editor while real
  // blocks remain available for localized validation.
  const properties = Array.isArray(filledFormData.properties) ? filledFormData.properties : []
  filledFormData.properties = properties.filter(property => {
    return property !== null && typeof property === 'object' && !Array.isArray(property)
  })

  // Ensure settings object exists and is a plain object (not a readonly proxy)
  ensureSettingsObject(filledFormData)

  return filledFormData
}

/**
 * Ensures the settings object exists and is a writable plain object.
 * This is crucial for reactive forms where settings might be undefined or a readonly proxy.
 * 
 * @param {Object} formData - The form data object
 */
export function ensureSettingsObject(formData) {
  if (!formData) return
  
  const s = formData.settings
  if (!s || typeof s !== 'object' || Array.isArray(s)) {
    formData.settings = {}
  } else if (Object.isFrozen(s) || !Object.isExtensible(s) || isReadonly(s)) {
    // If settings is readonly/frozen, create a new writable copy
    formData.settings = { ...s }
  }
}
