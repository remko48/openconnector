/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Synchronization } from '../../entities/index.js'

export const useSynchronizationStore = defineStore(
	'synchronization', {
		state: () => ({
			synchronizationItem: false,
			synchronizationList: [],
		}),
		actions: {
			setSynchronizationItem(synchronizationItem) {
				this.synchronizationItem = synchronizationItem && new Synchronization(synchronizationItem)
				console.log('Active synchronization item set to ' + synchronizationItem)
			},
			setSynchronizationList(synchronizationList) {
				this.synchronizationList = synchronizationList.map(
					(synchronizationItem) => new Synchronization(synchronizationItem),
				)
				console.log('Synchronization list set to ' + synchronizationList.length + ' items')
			},
			/* istanbul ignore next */ // ignore this for Jest until moved into a service
			async refreshSynchronizationList(search = null) {
				// @todo this might belong in a service?
				let endpoint = '/index.php/apps/openconnector/api/synchronizations'
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
									this.setSynchronizationList(data.results)
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
			// New function to get a single synchronization
			async getSynchronization(id) {
				const endpoint = `/index.php/apps/openconnector/api/synchronizations/${id}`
				try {
					const response = await fetch(endpoint, {
						method: 'GET',
					})
					const data = await response.json()
					this.setSynchronizationItem(data)
					return data
				} catch (err) {
					console.error(err)
					throw err
				}
			},
			// Delete a synchronization
			deleteSynchronization() {
				if (!this.synchronizationItem || !this.synchronizationItem.id) {
					throw new Error('No synchronization item to delete')
				}

				console.log('Deleting synchronization...')

				const endpoint = `/index.php/apps/openconnector/api/synchronizations/${this.synchronizationItem.id}`

				return fetch(endpoint, {
					method: 'DELETE',
				})
					.then((response) => {
						this.refreshSynchronizationList()
					})
					.catch((err) => {
						console.error('Error deleting synchronization:', err)
						throw err
					})
			},
			// Create or save a synchronization from store
			saveSynchronization() {
				if (!this.synchronizationItem) {
					throw new Error('No synchronization item to save')
				}

				console.log('Saving synchronization...')

				const isNewSynchronization = !this.synchronizationItem.id
				const endpoint = isNewSynchronization
					? '/index.php/apps/openconnector/api/synchronizations'
					: `/index.php/apps/openconnector/api/synchronizations/${this.synchronizationItem.id}`
				const method = isNewSynchronization ? 'POST' : 'PUT'

				// Create a copy of the synchronization item and remove empty properties
				const synchronizationToSave = { ...this.synchronizationItem }
				Object.keys(synchronizationToSave).forEach(key => {
					if (synchronizationToSave[key] === '' || (Array.isArray(synchronizationToSave[key]) && synchronizationToSave[key].length === 0)) {
						delete synchronizationToSave[key]
					}
				})

				return fetch(
					endpoint,
					{
						method,
						headers: {
							'Content-Type': 'application/json',
						},
						body: JSON.stringify(synchronizationToSave),
					},
				)
					.then((response) => response.json())
					.then((data) => {
						this.setSynchronizationItem(data)
						console.log('Synchronization saved')
						// Refresh the synchronization list
						return this.refreshSynchronizationList()
					})
					.catch((err) => {
						console.error('Error saving synchronization:', err)
						throw err
					})
			},
		},
	},
)
