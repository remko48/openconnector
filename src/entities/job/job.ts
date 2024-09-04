import { SafeParseReturnType, z } from 'zod'
import { TJob } from './job.types'

export class Job implements TJob {
    public id: string
    public name: string
    public description: string | null
    public reference: string | null
    public version: string
    public crontab: string
    public userId: string | null
    public throws: string[]
    public data: object | null
    public lastRun: string | null
    public nextRun: string | null
    public isEnabled: boolean | null
    public dateCreated: string | null
    public dateModified: string | null
    public listens: string[]
    public conditions: object | null
    public class: string | null
    public priority: number
    public async: boolean
    public configuration: object | null
    public isLockable: boolean
    public locked: string | null
    public lastRunTime: number | null
    public status: boolean | null
    public actionHandlerConfiguration: object | null

    constructor(job: TJob) {
        this.id = job.id || ''
        this.name = job.name || ''
        this.description = job.description || null
        this.reference = job.reference || null
        this.version = job.version || '0.0.0'
        this.crontab = job.crontab || '*/5 * * * *'
        this.userId = job.userId || null
        this.throws = job.throws || []
        this.data = job.data || null
        this.lastRun = job.lastRun || null
        this.nextRun = job.nextRun || null
        this.isEnabled = job.isEnabled ?? true
        this.dateCreated = job.dateCreated || null
        this.dateModified = job.dateModified || null
        this.listens = job.listens || []
        this.conditions = job.conditions || null
        this.class = job.class || null
        this.priority = job.priority || 1
        this.async = job.async || false
        this.configuration = job.configuration || null
        this.isLockable = job.isLockable || false
        this.locked = job.locked || null
        this.lastRunTime = job.lastRunTime || null
        this.status = job.status || null
        this.actionHandlerConfiguration = job.actionHandlerConfiguration || null
    }

    public validate(): SafeParseReturnType<TJob, unknown> {
        const schema = z.object({
            id: z.string().uuid(),
            name: z.string().max(255),
            version: z.string(),
        })

        return schema.safeParse({ ...this })
    }
}