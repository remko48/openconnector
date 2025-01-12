import { setActivePinia, createPinia } from 'pinia'

import { useSynchronizationStore } from './synchronization.js'
import { Synchronization, mockSynchronization } from '../../entities/index.js'

describe('Synchronization Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets synchronization item correctly', () => {
		const store = useSynchronizationStore()

		store.setSynchronizationItem(mockSynchronization()[0])

		expect(store.synchronizationItem).toBeInstanceOf(Synchronization)
		expect(store.synchronizationItem).toEqual(mockSynchronization()[0])

		expect(store.synchronizationItem.validate().success).toBe(true)
	})

	it('sets synchronization list correctly', () => {
		const store = useSynchronizationStore()

		store.setSynchronizationList(mockSynchronization())

		expect(store.synchronizationList).toHaveLength(mockSynchronization().length)

		store.synchronizationList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Synchronization)
			expect(item).toEqual(mockSynchronization()[index])
			expect(item.validate().success).toBe(true)
		})
	})

	// ... other tests ...
})
