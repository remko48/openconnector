/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useMappingStore } from './mapping.js'
import { Mapping, mockMapping } from '../../entities/index.js'

describe('Mapping Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets mapping item correctly', () => {
		const store = useMappingStore()

		store.setMappingItem(mockMapping()[0])

		expect(store.mappingItem).toBeInstanceOf(Mapping)
		expect(store.mappingItem).toEqual(mockMapping()[0])

		expect(store.mappingItem.validate().success).toBe(true)
	})

	it('sets mapping list correctly', () => {
		const store = useMappingStore()

		store.setMappingList(mockMapping())

		expect(store.mappingList).toHaveLength(mockMapping().length)

		store.mappingList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Mapping)
			expect(item).toEqual(mockMapping()[index])
			expect(item.validate().success).toBe(true)
		})
	})

	// ... other tests ...
})
