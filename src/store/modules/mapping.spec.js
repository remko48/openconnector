/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useMappingStore } from './mapping.js'
import { Mapping, mockMappings } from '../../entities/index.js'

describe('Mapping Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets mapping item correctly', () => {
		const store = useMappingStore()

		store.setMappingItem(mockMappings()[0])

		expect(store.mappingItem).toBeInstanceOf(Mapping)
		expect(store.mappingItem).toEqual(mockMappings()[0])

		expect(store.mappingItem.validate().success).toBe(true)
	})

	it('sets mapping list correctly', () => {
		const store = useMappingStore()

		store.setMappingList(mockMappings())

		expect(store.mappingList).toHaveLength(mockMappings().length)

		store.mappingList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Mapping)
			expect(item).toEqual(mockMappings()[index])
			expect(item.validate().success).toBe(true)
		})
	})

	// ... other tests ...
})
