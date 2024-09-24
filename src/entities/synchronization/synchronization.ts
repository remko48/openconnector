import { SafeParseReturnType, z } from 'zod'
import { TSynchronization } from './synchronization.types'

export class Synchronization implements TSynchronization {

	public id: string
	public entity: object
	public object: object | null
	public action: object | null
	public gateway: object | null
	public sourceObject: object | null
	public endpoint: string | null
	public sourceId: string | null
	public hash: string | null
	public sha: string | null
	public blocked: boolean
	public sourceLastChanged: string | null
	public lastChecked: string | null
	public lastSynced: string | null
	public dateCreated: string | null
	public dateModified: string | null
	public tryCounter: number
	public dontSyncBefore: string | null
	public mapping: object | null

	constructor(synchronization: TSynchronization) {
		this.id = synchronization.id || ''
		this.entity = synchronization.entity
		this.object = synchronization.object || null
		this.action = synchronization.action || null
		this.gateway = synchronization.gateway || null
		this.sourceObject = synchronization.sourceObject || null
		this.endpoint = synchronization.endpoint || null
		this.sourceId = synchronization.sourceId || null
		this.hash = synchronization.hash || null
		this.sha = synchronization.sha || null
		this.blocked = synchronization.blocked || false
		this.sourceLastChanged = synchronization.sourceLastChanged || null
		this.lastChecked = synchronization.lastChecked || null
		this.lastSynced = synchronization.lastSynced || null
		this.dateCreated = synchronization.dateCreated || null
		this.dateModified = synchronization.dateModified || null
		this.tryCounter = synchronization.tryCounter || 0
		this.dontSyncBefore = synchronization.dontSyncBefore || null
		this.mapping = synchronization.mapping || null
	}

	public validate(): SafeParseReturnType<TSynchronization, unknown> {
		const schema = z.object({
			id: z.string().uuid(),
			entity: z.object({}),
			blocked: z.boolean(),
			tryCounter: z.number().int(),
		})

		return schema.safeParse({ ...this })
	}

}
