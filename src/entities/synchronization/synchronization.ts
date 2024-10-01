import { SafeParseReturnType, z } from 'zod'
import { TSynchronization } from './synchronization.types'

export class Synchronization implements TSynchronization {

	public id: string
	public name: string
	public description: string
	public sourceId: string
	public sourceType: string
	public sourceHash: string
	public sourceTargetMapping: string
	public sourceConfig: object
	public targetId: string
	public targetType: string
	public targetHash: string
	public targetSourceMapping: string
	public targetConfig: object

	constructor(synchronization: TSynchronization) {
		this.id = synchronization.id || ''
		this.name = synchronization.name || ''
		this.description = synchronization.description || null
		this.sourceId = synchronization.sourceId || null
		this.sourceType = synchronization.sourceType || null
		this.sourceHash = synchronization.sourceHash || null
		this.sourceTargetMapping = synchronization.sourceTargetMapping || null
		this.sourceConfig = synchronization.sourceConfig || null
		this.targetId = synchronization.targetId || null
		this.targetType = synchronization.targetType || null
		this.targetHash = synchronization.targetHash || null
		this.targetSourceMapping = synchronization.targetSourceMapping || null
		this.targetConfig = synchronization.targetConfig || null

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
