import LeaveConfirmModal from '~/components/global/LeaveConfirmModal.vue'

/**
 * Asks, in a Forms4U-styled modal, whether to leave a page with unsaved changes.
 * Resolves true to leave; false when the user keeps editing or dismisses the
 * modal (Esc / click outside resolves undefined).
 *
 * The browser's own dialog on tab close/reload (onbeforeunload) cannot be styled.
 */
export function useLeaveConfirm() {
  const overlay = useOverlay()

  async function confirmLeave(props = {}) {
    // A fresh overlay per question: destroyOnClose removes it after it closes.
    const modal = overlay.create(LeaveConfirmModal, { destroyOnClose: true })
    const result = await modal.open(props).result
    return result === true
  }

  return { confirmLeave }
}
