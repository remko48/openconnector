/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Endpoint } from '../../entities/index.js'

export const useEndpointStore = defineStore(
	'endpoint', {
		state: () => ({
			endpointItem: false,
			endpointList: [],
		}),
		actions: {
			setEndpointItem(endpointItem) {
				this.endpointItem = endpointItem && new Endpoint(endpointItem)
				console.log('Active endpoint item set to ' + endpointItem)
			},
			setEndpointList(endpointList) {
				this.endpointList = endpointList.map(
					(endpointItem) => new Endpoint(endpointItem),
				)
				console.log('Endpoint list set to ' + endpointList.length + ' items')
			},
			/* istanbul ignore next */ // ignore this for Jest until moved into a service
			async refreshEndpointList(search = null) {
				// @todo this might belong in a service?
				let endpoint = '/index.php/apps/openconnector/api/endpoints'
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
									this.setEndpointList(data.results)
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
			// New function to get a single endpoint
			async getEndpoint(id) {
				const endpoint = `/index.php/apps/openconnector/api/endpoints/${id}`
				try {
					const response = await fetch(endpoint, {
						method: 'GET',
					})
					const data = await response.json()
					this.setEndpointItem(data)
					return data
				} catch (err) {
					console.error(err)
					throw err
				}
			},
			// Delete an endpoint
			deleteEndpoint() {
				if (!this.endpointItem || !this.endpointItem.id) {
					throw new Error('No endpoint item to delete')
				}

				console.log('Deleting endpoint...')

				const endpoint = `/index.php/apps/openconnector/api/endpoints/${this.endpointItem.id}`

				return fetch(endpoint, {
					method: 'DELETE',
				})
					.then((response) => {
						this.refreshEndpointList()
					})
					.catch((err) => {
						console.error('Error deleting endpoint:', err)
						throw err
					})
			},
			// Create or save an endpoint from store
			saveEndpoint() {
				if (!this.endpointItem) {
					throw new Error('No endpoint item to save')
				}

				console.log('Saving endpoint...')

				const isNewEndpoint = !this.endpointItem.id
				const endpoint = isNewEndpoint
					? '/index.php/apps/openconnector/api/endpoints'
					: `/index.php/apps/openconnector/api/endpoints/${this.endpointItem.id}`
				const method = isNewEndpoint ? 'POST' : 'PUT'

				// Create a copy of the endpoint item and remove empty properties
				const endpointToSave = { ...this.endpointItem }
				Object.keys(endpointToSave).forEach(key => {
					if (endpointToSave[key] === '' || (Array.isArray(endpointToSave[key]) && !endpointToSave[key].length)) {
						delete endpointToSave[key]
					}
				})

				return fetch(
					endpoint,
					{
						method,
						headers: {
							'Content-Type': 'application/json',
						},
						body: JSON.stringify(endpointToSave),
					},
				)
					.then((response) => response.json())
					.then((data) => {
						this.setEndpointItem(data)
						console.log('Endpoint saved')
						// Refresh the endpoint list
						return this.refreshEndpointList()
					})
					.catch((err) => {
						console.error('Error saving endpoint:', err)
						throw err
					})
			},
		},
	},
)
