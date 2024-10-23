import { SafeParseReturnType, z } from 'zod'
import { TConsumer } from './consumer.types'
import getValidISOstring from '../../services/getValidISOstring'

export class Consumer implements TConsumer {

	public id: number
	public uuid: string
	public name: string
	public description: string
	public domains: string[]
	public ips: string[]
	public authorizationType: 'none' | 'basic' | 'bearer' | 'apiKey' | 'oauth2' | 'jwt'
	public authorizationConfiguration: string[][]
	public created: string
	public updated: string

	constructor(consumer: TConsumer) {
		this.id = consumer.id || null
		this.uuid = consumer.uuid || ''
		this.name = consumer.name || ''
		this.description = consumer.description || ''
		this.domains = consumer.domains || []
		this.ips = consumer.ips || []
		this.authorizationType = consumer.authorizationType || 'basic'
		this.authorizationConfiguration = consumer.authorizationConfiguration || []

		/* Convert dates back to valid javascript ISO date strings */
		this.created = getValidISOstring(consumer.created)
		this.updated = getValidISOstring(consumer.updated)
	}

	public validate(): SafeParseReturnType<TConsumer, unknown> {
		const schema = z.object({
			id: z.number().or(z.null()),
			uuid: z.string().uuid().or(z.literal('')).optional(),
			name: z.string().max(255),
			description: z.string(),
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
