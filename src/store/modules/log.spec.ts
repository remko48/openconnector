import { setActivePinia, createPinia } from 'pinia'

import { useLogStore } from './log.js'
import { Log, mockLog } from '../../entities/index.js'

describe('Log Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets log item correctly', () => {
		const store = useLogStore()

		store.setLogItem(mockLog()[0])

		expect(store.logItem).toBeInstanceOf(Log)
		expect(store.logItem).toEqual(mockLog()[0])

		expect(store.logItem.validate().success).toBe(true)
	})

	it('sets log list correctly', () => {
		const store = useLogStore()

		store.setLogList(mockLog())

		expect(store.logList).toHaveLength(mockLog().length)

		store.logList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Log)
			expect(item).toEqual(mockLog()[index])
			expect(item.validate().success).toBe(true)
		})
	})

	// ... other tests ...
})
