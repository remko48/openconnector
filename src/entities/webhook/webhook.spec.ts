import { Webhook } from './webhook'
import { mockWebhook } from './webhook.mock'

describe('Webhook Entity', () => {
	it('create Webhook entity with full data', () => {
		const webhook = new Webhook(mockWebhook()[0])

		expect(webhook).toBeInstanceOf(Webhook)
		expect(webhook).toEqual(mockWebhook()[0])

		expect(webhook.validate().success).toBe(true)
	})

	it('create Webhook entity with partial data', () => {
		const webhook = new Webhook(mockWebhook()[1])

		expect(webhook).toBeInstanceOf(Webhook)
		expect(webhook).toEqual(mockWebhook()[1])

		expect(webhook.validate().success).toBe(true)
	})
})
