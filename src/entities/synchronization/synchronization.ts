import { SafeParseReturnType, z } from 'zod'
import { TSynchronization } from './synchronization.types'

export class Synchronization implements TSynchronization {

	public id?: string
	public name: string
	public description: string
	public sourceId: string
	public sourceType: string
	public sourceHash?: string
	public sourceTargetMapping: string
	public sourceConfig?: object
	public sourceLastChanged?: string
	public sourceLastChecked?: string
	public sourceLastSynced?: string
	public targetId: string
	public targetType: string
	public targetHash?: string
	public targetSourceMapping: string
	public targetConfig?: object
	public targetLastChanged?: string
	public targetLastChecked?: string
	public targetLastSynced?: string
	public created: string
	public updated: string

	constructor(synchronization: TSynchronization) {
		this.id = synchronization.id || ''
		this.name = synchronization.name || ''
		this.description = synchronization.description || ''
		this.sourceId = synchronization.sourceId || ''
		this.sourceType = synchronization.sourceType || ''
		this.sourceHash = synchronization.sourceHash || ''
		this.sourceTargetMapping = synchronization.sourceTargetMapping || ''
		this.sourceConfig = synchronization.sourceConfig || {}
		this.sourceLastChanged = synchronization.sourceLastChanged || ''
		this.sourceLastChecked = synchronization.sourceLastChecked || ''
		this.sourceLastSynced = synchronization.sourceLastSynced || ''
		this.targetId = synchronization.targetId || ''
		this.targetType = synchronization.targetType || ''
		this.targetHash = synchronization.targetHash || ''
		this.targetSourceMapping = synchronization.targetSourceMapping || ''
		this.targetConfig = synchronization.targetConfig || {}
		this.targetLastChanged = synchronization.targetLastChanged || ''
		this.targetLastChecked = synchronization.targetLastChecked || ''
		this.targetLastSynced = synchronization.targetLastSynced || ''
		this.created = synchronization.created || ''
		this.updated = synchronization.updated || ''
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
