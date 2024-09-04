import { Synchronization } from './synchronization'
import { mockSynchronization } from './synchronization.mock'

describe('Synchronization Entity', () => {
    it('create Synchronization entity with full data', () => {
        const synchronization = new Synchronization(mockSynchronization()[0])

        expect(synchronization).toBeInstanceOf(Synchronization)
        expect(synchronization).toEqual(mockSynchronization()[0])

        expect(synchronization.validate().success).toBe(true)
    })

    it('create Synchronization entity with partial data', () => {
        const synchronization = new Synchronization(mockSynchronization()[1])

        expect(synchronization).toBeInstanceOf(Synchronization)
        expect(synchronization).toEqual(mockSynchronization()[1])

        expect(synchronization.validate().success).toBe(true)
    })
})