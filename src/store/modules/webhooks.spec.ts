import { setActivePinia, createPinia } from 'pinia'

import { useWebhookStore } from './webhooks.js'
import { Webhook, mockWebhook } from '../../entities/index.js'

describe('Webhook Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets webhook item correctly', () => {
		const store = useWebhookStore()

		store.setWebhookItem(mockWebhook()[0])

		expect(store.webhookItem).toBeInstanceOf(Webhook)
		expect(store.webhookItem).toEqual(mockWebhook()[0])

		expect(store.webhookItem.validate().success).toBe(true)
	})

	it('sets webhook list correctly', () => {
		const store = useWebhookStore()

		store.setWebhookList(mockWebhook())

		expect(store.webhookList).toHaveLength(mockWebhook().length)

		store.webhookList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Webhook)
			expect(item).toEqual(mockWebhook()[index])
			expect(item.validate().success).toBe(true)
		})
	})

	// ... other tests ...
})
