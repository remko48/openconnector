/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useSourceStore } from './source.js'
import { Source, mockSources } from '../../entities/index.js'

describe('Source Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets source item correctly', () => {
		const store = useSourceStore()

		store.setSourceItem(mockSources()[0])

		expect(store.sourceItem).toBeInstanceOf(Source)
		expect(store.sourceItem).toEqual(mockSources()[0])

		expect(store.sourceItem.validate().success).toBe(true)
	})

	it('sets source list correctly', () => {
		const store = useSourceStore()

		store.setSourceList(mockSources())

		expect(store.sourceList).toHaveLength(mockSources().length)

		// Test each item in the list
		store.sourceList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Source)
			expect(item).toEqual(mockSources()[index])
			expect(item.validate().success).toBe(true)
		})
	})

	// Add more tests for other actions (refreshSourceList, deleteSource, saveSource)
})
