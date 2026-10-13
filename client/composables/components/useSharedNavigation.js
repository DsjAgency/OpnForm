export const useSharedNavigation = () => {
  // Default button configuration
  const defaultButtonProps = {
    variant: 'ghost',
    activeVariant: 'soft', 
    color: 'neutral',
    block: true,
  }

  // Helper function to apply defaults to navigation items
  const createNavItem = (item) => {
    const baseItem = {
      ...defaultButtonProps,
      ...item
    }
    
    // Add custom classes to darken ghost/soft variants for better visibility on neutral-100 background
    const customClasses = ['group', item.class].filter(Boolean)
    
    // For ghost variant (default), darken hover state
    if (baseItem.variant === 'ghost' && baseItem.color === 'neutral') {
      customClasses.push('hover:bg-neutral-200/60 transition-colors duration-150')
      baseItem.ui = {
        ...baseItem.ui,
        leadingIcon: baseItem.active
          ? 'text-primary-700'
          : 'text-neutral-400 group-hover:text-neutral-600 transition-colors'
      }
    }

    // Active item: brand tint with a small accent bar on the left
    if (baseItem.active && baseItem.activeVariant === 'soft' && baseItem.color === 'neutral') {
      customClasses.push('relative !bg-primary-50 !text-primary-900 font-medium before:absolute before:left-0 before:top-1.5 before:bottom-1.5 before:w-[3px] before:rounded-full before:bg-primary-700')
    }
    
    // For primary color buttons, ensure good contrast
    if (baseItem.color === 'primary') {
      if (baseItem.variant === 'ghost') {
        customClasses.push('hover:bg-primary-100/80')
      }
      if (baseItem.active && baseItem.activeVariant === 'soft') {
        customClasses.push('data-[active=true]:bg-primary-100/90')
      }
    }
    
    return {
      ...baseItem,
      class: customClasses.length > 0 ? customClasses.join(' ') : undefined
    }
  }

  // Product and Help sections removed for the internal Cidadania4U build
  const sharedNavigationSections = computed(() => [])

  return {
    sharedNavigationSections,
    createNavItem,
    defaultButtonProps
  }
} 