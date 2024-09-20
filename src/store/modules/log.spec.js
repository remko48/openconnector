/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useLogStore } from './log.js'
import { Log, mockLogs } from '../../entities/index.js'

describe('Log Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets log item correctly', () => {
		const store = useLogStore()

		store.setLogItem(mockLogs()[0])

		expect(store.logItem).toBeInstanceOf(Log)
		expect(store.logItem).toEqual(mockLogs()[0])

		expect(store.logItem.validate().success).toBe(true)
	})

	it('sets log list correctly', () => {
		const store = useLogStore()

		store.setLogList(mockLogs())

		expect(store.logList).toHaveLength(mockLogs().length)

		store.logList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Log)
			expect(item).toEqual(mockLogs()[index])
			expect(item.validate().success).toBe(true)
		})
	})

	// ... other tests ...
})
