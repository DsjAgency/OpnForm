// Forms4U page backgrounds for public forms (form.settings.background).
// Values: a preset id, 'custom' (uses settings.background_image) or 'none'.
// When nothing was chosen, light forms get the light wallpaper and dark
// forms the wine one.
export const FORM_BACKGROUNDS = [
  {
    id: 'forms4u-claro',
    label: 'Forms4U claro',
    url: '/img/backgrounds/forms4u-claro.png',
    color: '#F7F1EE',
    thumb: '/img/backgrounds/forms4u-claro-thumb.webp',
  },
  {
    id: 'forms4u-vinho',
    label: 'Forms4U vinho',
    url: '/img/backgrounds/forms4u-vinho.png',
    color: '#6B0F2B',
    thumb: '/img/backgrounds/forms4u-vinho-thumb.webp',
  },
]

export function resolveFormBackground(form, isDark = false) {
  const choice = form?.settings?.background

  if (choice === 'none') {
    return null
  }

  if (choice === 'custom') {
    const url = form?.settings?.background_image
    return url ? { url } : null
  }

  const preset = FORM_BACKGROUNDS.find((background) => background.id === choice)
    ?? FORM_BACKGROUNDS[isDark ? 1 : 0]

  return { url: preset.url, color: preset.color }
}

// On the live page the image covers the viewport and stays fixed while the
// form scrolls. Inside the editor preview (a scrolling box) it is drawn at the
// top, full width, with the wallpaper color below it.
export function formBackgroundStyle(background, { fixed = true } = {}) {
  if (!background?.url) {
    return {}
  }

  const style = {
    backgroundImage: `url("${encodeURI(background.url)}")`,
    backgroundRepeat: 'no-repeat',
    // Anchored top-left so the Forms4U logo in the wallpapers is never cropped
    backgroundPosition: 'left top',
  }
  if (background.color) {
    style.backgroundColor = background.color
  }

  return fixed
    ? { ...style, backgroundSize: 'cover', backgroundAttachment: 'fixed' }
    : { ...style, backgroundSize: '100% auto' }
}
