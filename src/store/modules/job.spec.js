import { setActivePinia, createPinia } from 'pinia'

import { useJobStore } from './job.js'
import { Job, mockJob } from '../../entities/index.js'

describe('Job Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets job item correctly', () => {
		const store = useJobStore()

		store.setJobItem(mockJob()[0])

		expect(store.jobItem).toBeInstanceOf(Job)
		expect(store.jobItem).toEqual(mockJob()[0])

		expect(store.jobItem.validate().success).toBe(true)
	})

	it('sets job list correctly', () => {
		const store = useJobStore()

		store.setJobList(mockJob())

		expect(store.jobList).toHaveLength(mockJob().length)

		store.jobList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Job)
			expect(item).toEqual(mockJob()[index])
			expect(item.validate().success).toBe(true)
		})
	})

	// ... other tests ...
})
