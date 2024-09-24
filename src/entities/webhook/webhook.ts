import { SafeParseReturnType, z } from 'zod'
import { TWebhook } from './webhook.types'

export class Webhook implements TWebhook {

	public id: string
	public name: string
	public description: string | null
	public version: string
	public url: string
	public isEnabled: boolean

	constructor(webhook: TWebhook) {
		this.id = webhook.id || ''
		this.name = webhook.name || ''
		this.description = webhook.description || null
		this.version = webhook.version || '0.0.0'
		this.url = webhook.url || ''
		this.isEnabled = webhook.isEnabled ?? true
	}

	public validate(): SafeParseReturnType<TWebhook, unknown> {
		const schema = z.object({
			id: z.string().uuid(),
			name: z.string().max(255),
			version: z.string(),
			url: z.string().url(),
			isEnabled: z.boolean()
		})

		return schema.safeParse({ ...this })
	}

}
