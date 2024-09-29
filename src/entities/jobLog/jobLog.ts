import { SafeParseReturnType, z } from 'zod'
import { TJobLog } from './jobLog.types'

export class JobLog implements TJobLog {

	public id?: string
	public jobId?: string
	public jobListId?: string
	public jobClass?: string
	public arguments?: object | null
	public executionTime?: number
	public userId?: string | null
	public lastRun?: string | null
	public nextRun?: string | null
	public created?: string | null

	constructor(jobLog: TJobLog) {
		this.id = jobLog.id
		this.jobId = jobLog.jobId
		this.jobListId = jobLog.jobListId
		this.jobClass = jobLog.jobClass
		this.arguments = jobLog.arguments
		this.executionTime = jobLog.executionTime
		this.userId = jobLog.userId
		this.lastRun = jobLog.lastRun
		this.nextRun = jobLog.nextRun
		this.created = jobLog.created
	}

	public validate(): SafeParseReturnType<TJobLog, unknown> {
		const schema = z.object({
			id: z.string().uuid().optional(),
			jobId: z.string().optional(),
			jobListId: z.string().optional(),
			jobClass: z.string().optional(),
			arguments: z.record(z.any()).nullable().optional(),
			executionTime: z.number().optional(),
			userId: z.string().nullable().optional(),
			lastRun: z.string().nullable().optional(),
			nextRun: z.string().nullable().optional(),
			created: z.string().nullable().optional()
		})

		return schema.safeParse({ ...this })
	}

}
