import { Log } from './log'
import { mockLog } from './log.mock'

describe('Log Entity', () => {
	it('create Log entity with full data', () => {
		const log = new Log(mockLog()[0])

		expect(log).toBeInstanceOf(Log)
		expect(log).toEqual(mockLog()[0])

		expect(log.validate().success).toBe(true)
	})

	it('create Log entity with partial data', () => {
		const log = new Log(mockLog()[1])

		expect(log).toBeInstanceOf(Log)
		expect(log).toEqual(mockLog()[1])

		expect(log.validate().success).toBe(true)
	})
})
