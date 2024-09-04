/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useJobStore } from './job.js'
import { Job, mockJobs } from '../../entities/index.js'

describe('Job Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets job item correctly', () => {
		const store = useJobStore()

		store.setJobItem(mockJobs()[0])

		expect(store.jobItem).toBeInstanceOf(Job)
		expect(store.jobItem).toEqual(mockJobs()[0])

		expect(store.jobItem.validate().success).toBe(true)
	})

	it('sets job list correctly', () => {
		const store = useJobStore()

		store.setJobList(mockJobs())

		expect(store.jobList).toHaveLength(mockJobs().length)

		store.jobList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Job)
			expect(item).toEqual(mockJobs()[index])
			expect(item.validate().success).toBe(true)
		})
	})

	// ... other tests ...
})