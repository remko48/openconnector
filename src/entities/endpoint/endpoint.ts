import { SafeParseReturnType, z } from 'zod'
import { TEndpoint } from './endpoint.types'

export class Endpoint implements TEndpoint {

	public id: string
	public name: string
	public description: string | null
	public version: string
	public path: string
	public method: string
	public isEnabled: boolean
	public dateCreated: string | null
	public dateModified: string | null
	public headers: object | null
	public parameters: object | null
	public responseSchema: object | null
	public authentication: string | null
	public rateLimit: number | null
	public caching: boolean
	public timeout: number | null

	constructor(endpoint: TEndpoint) {
		this.id = endpoint.id || ''
		this.name = endpoint.name || ''
		this.description = endpoint.description || null
		this.version = endpoint.version || '0.0.0'
		this.path = endpoint.path || ''
		this.method = endpoint.method || 'GET'
		this.isEnabled = endpoint.isEnabled ?? true
		this.dateCreated = endpoint.dateCreated || null
		this.dateModified = endpoint.dateModified || null
		this.headers = endpoint.headers || null
		this.parameters = endpoint.parameters || null
		this.responseSchema = endpoint.responseSchema || null
		this.authentication = endpoint.authentication || null
		this.rateLimit = endpoint.rateLimit || null
		this.caching = endpoint.caching ?? false
		this.timeout = endpoint.timeout || null
	}

	public validate(): SafeParseReturnType<TEndpoint, unknown> {
		const schema = z.object({
			id: z.string().uuid(),
			name: z.string().max(255),
			version: z.string(),
			path: z.string(),
			method: z.enum(['GET', 'POST', 'PUT', 'DELETE', 'PATCH']),
			isEnabled: z.boolean()
		})

		return schema.safeParse({ ...this })
	}

}
