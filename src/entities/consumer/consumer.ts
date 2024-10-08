import { SafeParseReturnType, z } from 'zod'
import { TConsumer } from './consumer.types'

export class Consumer implements TConsumer {

	public id: string
	public uuid: string
	public name: string
	public description: string
	public reference: string
	public version: string
	public domains: string[]
	public ips: string[]
	public authorizationType: 'none' | 'basic' | 'bearer' | 'apiKey' | 'oauth2' | 'jwt'
	public authorizationConfiguration: string[][]
	public created: string
	public updated: string

	constructor(consumer: TConsumer) {
		this.id = consumer.id || ''
		this.uuid = consumer.id || ''
		this.name = consumer.name || ''
		this.description = consumer.description || ''
		this.reference = consumer.reference || ''
		this.version = consumer.version || ''
		this.domains = consumer.domains || []
		this.ips = consumer.ips || []
		this.authorizationType = consumer.authorizationType || 'basic'
		this.authorizationConfiguration = consumer.authorizationConfiguration || []
		this.created = consumer.created || ''
		this.updated = consumer.updated || ''
	}

	public validate(): SafeParseReturnType<TConsumer, unknown> {
		const schema = z.object({
			id: z.string().optional(),
			uuid: z.string().uuid().or(z.literal('')).optional(),
			name: z.string().max(255),
			description: z.string(),
			reference: z.string(),
			version: z.string(),
			domains: z.array(z.string()),
			ips: z.array(z.string()),
			authorizationType: z.enum(['none', 'basic', 'bearer', 'apiKey', 'oauth2', 'jwt']),
			authorizationConfiguration: z.array(z.array(z.string())),
			created: z.string().datetime().or(z.literal('')).optional(),
			updated: z.string().datetime().or(z.literal('')).optional(),
		})

		return schema.safeParse({ ...this })
	}

}
