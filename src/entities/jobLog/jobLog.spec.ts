import { JobLog } from './jobLog'
import { mockJobLog } from './jobLog.mock'

describe('JobLog Entity', () => {
	it('create JobLog entity with full data', () => {
		const jobLog = new JobLog(mockJobLog()[0])

		expect(jobLog).toBeInstanceOf(JobLog)
		expect(jobLog).toEqual(mockJobLog()[0])

		expect(jobLog.validate().success).toBe(true)
	})

	it('create JobLog entity with partial data', () => {
		const jobLog = new JobLog(mockJobLog()[1])

		expect(jobLog).toBeInstanceOf(JobLog)
		expect(jobLog).toEqual(mockJobLog()[1])

		expect(jobLog.validate().success).toBe(true)
	})
})
