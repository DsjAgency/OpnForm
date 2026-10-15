import { beforeEach, describe, expect, it, vi } from 'vitest'

vi.mock('~/components/global/LeaveConfirmModal.vue', () => ({ default: { name: 'LeaveConfirmModal' } }))

const create = vi.fn()
vi.stubGlobal('useOverlay', () => ({ create }))

const { useLeaveConfirm } = await import('../../composables/useLeaveConfirm.js')

// Next modal created will resolve with this value (true = leave, false = keep editing, undefined = Esc/click outside)
function answerWith(value: boolean | undefined) {
  create.mockReturnValueOnce({ open: vi.fn(() => ({ result: Promise.resolve(value) })) })
}

describe('useLeaveConfirm', () => {
  beforeEach(() => {
    create.mockReset()
  })

  it('leaves only when the user confirms', async () => {
    const { confirmLeave } = useLeaveConfirm()

    answerWith(true)
    expect(await confirmLeave()).toBe(true)

    answerWith(false)
    expect(await confirmLeave()).toBe(false)

    answerWith(undefined)
    expect(await confirmLeave()).toBe(false)
  })

  it('opens a fresh, self-destroying modal for every question', async () => {
    const { confirmLeave } = useLeaveConfirm()
    answerWith(false)
    answerWith(false)

    await confirmLeave()
    await confirmLeave()

    expect(create).toHaveBeenCalledTimes(2)
    expect(create).toHaveBeenCalledWith(expect.anything(), { destroyOnClose: true })
  })
})
