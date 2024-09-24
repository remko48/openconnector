import { Job } from './job'
import { mockJob } from './job.mock'

describe('Job Entity', () => {
	it('create Job entity with full data', () => {
		const job = new Job(mockJob()[0])

		expect(job).toBeInstanceOf(Job)
		expect(job).toEqual(mockJob()[0])

		expect(job.validate().success).toBe(true)
	})

	it('create Job entity with partial data', () => {
		const job = new Job(mockJob()[1])

		expect(job).toBeInstanceOf(Job)
		expect(job).toEqual(mockJob()[1])

		expect(job.validate().success).toBe(true)
	})
})
