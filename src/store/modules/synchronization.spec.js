/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useSynchronizationStore } from './synchronization.js'
import { Synchronization, mockSynchronizations } from '../../entities/index.js'

describe('Synchronization Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets synchronization item correctly', () => {
		const store = useSynchronizationStore()

		store.setSynchronizationItem(mockSynchronizations()[0])

		expect(store.synchronizationItem).toBeInstanceOf(Synchronization)
		expect(store.synchronizationItem).toEqual(mockSynchronizations()[0])

		expect(store.synchronizationItem.validate().success).toBe(true)
	})

	it('sets synchronization list correctly', () => {
		const store = useSynchronizationStore()

		store.setSynchronizationList(mockSynchronizations())

		expect(store.synchronizationList).toHaveLength(mockSynchronizations().length)

		store.synchronizationList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Synchronization)
			expect(item).toEqual(mockSynchronizations()[index])
			expect(item.validate().success).toBe(true)
		})
	})

	// ... other tests ...
})