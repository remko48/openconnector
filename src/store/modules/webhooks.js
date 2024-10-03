/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Webhook } from '../../entities/index.js'

export const useWebhookStore = defineStore(
	'webhook', {
		state: () => ({
			webhookItem: false,
			webhookList: [],
		}),
		actions: {
			setWebhookItem(webhookItem) {
				this.webhookItem = webhookItem && new Webhook(webhookItem)
				console.log('Active webhook item set to ' + webhookItem)
			},
			setWebhookList(webhookList) {
				this.webhookList = webhookList.map(
					(webhookItem) => new Webhook(webhookItem),
				)
				console.log('Webhook list set to ' + webhookList.length + ' items')
			},
			/* istanbul ignore next */ // ignore this for Jest until moved into a service
			async refreshWebhookList(search = null) {
				// @todo this might belong in a service?
				let endpoint = '/index.php/apps/openconnector/api/webhooks'
				if (search !== null && search !== '') {
					endpoint = endpoint + '?_search=' + search
				}
				return fetch(endpoint, {
					method: 'GET',
				})
					.then(
						(response) => {
							response.json().then(
								(data) => {
									this.setWebhookList(data.results)
								},
							)
						},
					)
					.catch(
						(err) => {
							console.error(err)
						},
					)
			},
			// New function to get a single webhook
			async getWebhook(id) {
				const endpoint = `/index.php/apps/openconnector/api/webhooks/${id}`
				try {
					const response = await fetch(endpoint, {
						method: 'GET',
					})
					const data = await response.json()
					this.setWebhookItem(data)
					return data
				} catch (err) {
					console.error(err)
					throw err
				}
			},
			// Delete a webhook
			deleteWebhook() {
				if (!this.webhookItem || !this.webhookItem.id) {
					throw new Error('No webhook item to delete')
				}

				console.log('Deleting webhook...')

				const endpoint = `/index.php/apps/openconnector/api/webhooks/${this.webhookItem.id}`

				return fetch(endpoint, {
					method: 'DELETE',
				})
					.then((response) => {
						this.refreshWebhookList()
					})
					.catch((err) => {
						console.error('Error deleting webhook:', err)
						throw err
					})
			},
			// Create or save a webhook from store
			saveWebhook() {
				if (!this.webhookItem) {
					throw new Error('No webhook item to save')
				}

				console.log('Saving webhook...')

				const isNewWebhook = !this.webhookItem.id
				const endpoint = isNewWebhook
					? '/index.php/apps/openconnector/api/webhooks'
					: `/index.php/apps/openconnector/api/webhooks/${this.webhookItem.id}`
				const method = isNewWebhook ? 'POST' : 'PUT'

				// Create a copy of the webhook item and remove empty properties
				const webhookToSave = { ...this.webhookItem }
				Object.keys(webhookToSave).forEach(key => {
					if (webhookToSave[key] === '' || (Array.isArray(webhookToSave[key]) && !webhookToSave[key].length)) {
						delete webhookToSave[key]
					}
				})

				return fetch(
					endpoint,
					{
						method,
						headers: {
							'Content-Type': 'application/json',
						},
						body: JSON.stringify(webhookToSave),
					},
				)
					.then((response) => response.json())
					.then((data) => {
						this.setWebhookItem(data)
						console.log('Webhook saved')
						// Refresh the webhook list
						return this.refreshWebhookList()
					})
					.catch((err) => {
						console.error('Error saving webhook:', err)
						throw err
					})
			},
		},
	},
)
