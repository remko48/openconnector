import { Webhook } from './webhook'
import { TWebhook } from './webhook.types'

export const mockWebhookData = (): TWebhook[] => [
	{
		id: '5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f',
		name: 'Payment Notification',
		description: 'Receives payment notifications from payment gateway',
		version: '1.0.0',
		url: 'https://api.example.com/payment-webhook',
		isEnabled: true,
	},
	{
		id: '4c3edd34-a90d-4d2a-8894-adb5836ecde8',
		name: 'Inventory Update',
		description: 'Receives inventory updates from supplier',
		version: '1.1.0',
		url: 'https://api.example.com/inventory-webhook',
		isEnabled: true,
	},
]

export const mockWebhook = (data: TWebhook[] = mockWebhookData()): TWebhook[] => data.map(item => new Webhook(item))
