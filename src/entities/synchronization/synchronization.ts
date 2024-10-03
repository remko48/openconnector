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
		this.description = synchronization.description || ''
		this.sourceId = synchronization.sourceId || ''
		this.sourceType = synchronization.sourceType || ''
		this.sourceHash = synchronization.sourceHash || ''
		this.sourceTargetMapping = synchronization.sourceTargetMapping || ''
		this.sourceConfig = synchronization.sourceConfig || {}
		this.targetId = synchronization.targetId || ''
		this.targetType = synchronization.targetType || ''
		this.targetHash = synchronization.targetHash || ''
		this.targetSourceMapping = synchronization.targetSourceMapping || ''
		this.targetConfig = synchronization.targetConfig || {}

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
