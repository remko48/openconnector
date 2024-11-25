/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Source, TSource } from '../../entities/index.js'

export const useSourceStore = defineStore(
	'source', {
		state: () => ({
			sourceItem: false,
			sourceTest: false,
			sourceList: [],
			sourceLog: false,
			sourceLogs: [],
			sourceConfigurationKey: null,
		}),
		actions: {
			setSourceItem(sourceItem) {
				this.sourceItem = sourceItem && new Source(sourceItem)
				console.log('Active source item set to ' + sourceItem)
				this.refreshSourceLogs()

			},
			setSourceTest(sourceTest) {
				this.sourceTest = sourceTest
				console.log('Source test set to ' + sourceTest)
			},
			setSourceList(sourceList) {
				this.sourceList = sourceList.map(
					(sourceItem) => new Source(sourceItem),
				)
				console.log('Source list set to ' + sourceList.length + ' items')
			},
			setSourceLog(sourceLog) {
				this.sourceLog = sourceLog
				console.log('Source log set')
			},
			setSourceLogs(sourceLogs) {
				this.sourceLogs = sourceLogs
				console.log('Source logs set to ' + sourceLogs.length + ' items')
			},
			setSourceConfigurationKey(sourceConfigurationKey) {
				this.sourceConfigurationKey = sourceConfigurationKey
				console.log('Source configuration key set to ' + sourceConfigurationKey)
			},
			/**
			 * Refreshes the source list by fetching data from the API.
			 *
			 * @param { string | null } search - The search query to filter sources.
			 * @return { Promise<{ response: Response, data: Array<JSON>, entities: Array<Source> }> } The response, data, and entities.
			 */
			async refreshSourceList(search = null) {
				// @todo this might belong in a service?
				let endpoint = '/index.php/apps/openconnector/api/sources'
				if (search !== null && search !== '') {
					endpoint = endpoint + '?_search=' + search
				}

				const response = await fetch(endpoint, {
					method: 'GET',
				})

				const data = (await response.json()).results
				const entities = data.map(item => new Source(item))

				this.setSourceList(entities)

				return { response, data, entities }
			},
			// New function to get a single source
			async getSource(id) {
				const endpoint = `/index.php/apps/openconnector/api/sources/${id}`
				try {
					const response = await fetch(endpoint, {
						method: 'GET',
					})
					const data = await response.json()
					this.setSourceItem(data)
					return data
				} catch (err) {
					console.error(err)
					throw err
				}
			},
			// New function to get source logs
			async refreshSourceLogs() {
				if (!this.sourceItem?.id) {
					return console.warn('No source item to refresh logs')
				}
				const endpoint = `/index.php/apps/openconnector/api/sources-logs/${this.sourceItem.id}`
				try {
					const response = await fetch(endpoint, {
						method: 'GET',
					})
					const data = await response.json()
					this.setSourceLogs(data)
					return data
				} catch (err) {
					console.error(err)
					throw err
				}
			},
			// Delete a source
			deleteSource() {
				if (!this.sourceItem || !this.sourceItem.id) {
					throw new Error('No source item to delete')
				}

				console.log('Deleting source...')

				const endpoint = `/index.php/apps/openconnector/api/sources/${this.sourceItem.id}`

				return fetch(endpoint, {
					method: 'DELETE',
				})
					.then((response) => {
						this.refreshSourceList()
					})
					.catch((err) => {
						console.error('Error deleting source:', err)
						throw err
					})
			},
			// Test a source
			testSource(testSourceItem) {
				if (!this.sourceItem) {
					throw new Error('No source item to test')
				}
				if (!testSourceItem) {
					throw new Error('No testobject to test')
				}

				console.log('Testing source...')

				const endpoint = `/index.php/apps/openconnector/api/source-test/${this.sourceItem.id}`

				return fetch(endpoint, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(testSourceItem),
				})
					.then((response) => response.json())
					.then((data) => {
						this.setSourceTest(data)
						console.log('Source tested')
						// Refresh the source list
						this.refreshSourceLogs()
					})
					.catch((err) => {
						console.error('Error saving source:', err)
						this.refreshSourceLogs()
						throw err
					})
			},
			/**
			 * Saves a source item to the server. This function handles both the creation of new sources
			 * and the updating of existing ones. It sends a POST request for new sources and a PUT request
			 * for existing sources. The function also removes any empty properties and specific fields
			 * like `dateCreated` and `dateModified` before sending the data to the server.
			 *
			 * @async
			 * @param {TSource} sourceItem - The source item to be saved. This object should contain all the necessary
			 *                              properties of a source, including an `id` if it is an existing source.
			 * @return {Promise<{ response: Response, data: TSource, entity: Source }>}
			 *                            A promise that resolves to an object containing the server response, the data
			 *                            returned by the server, and the entity created from the data.
			 * @throws Throws an error if the `sourceItem` is not provided.
			 */
			async saveSource(sourceItem) {
				if (!sourceItem) {
					throw new Error('No source item to save')
				}

				console.log('Saving source...')

				// Determine if the source is new or existing based on the presence of an id
				const isNewSource = !sourceItem.id
				const endpoint = isNewSource
					? '/index.php/apps/openconnector/api/sources'
					: `/index.php/apps/openconnector/api/sources/${sourceItem.id}`
				const method = isNewSource ? 'POST' : 'PUT'

				// Create a copy of the source item and remove empty properties
				const sourceToSave = { ...sourceItem }
				Object.keys(sourceToSave).forEach(key => {
					if (sourceToSave[key] === '' || (Array.isArray(sourceToSave[key]) && !sourceToSave[key].length)) {
						delete sourceToSave[key]
					}
				})

				// Remove the dateCreated and dateModified fields
				delete sourceToSave.dateCreated
				delete sourceToSave.dateModified

				// Send the request to the server
				const response = await fetch(
					endpoint,
					{
						method,
						headers: {
							'Content-Type': 'application/json',
						},
						body: JSON.stringify(sourceToSave),
					},
				)

				// Parse the response data
				const data = await response.json()
				const entity = new Source(data)

				// Update the local state with the new or updated source
				this.setSourceItem(entity)
				this.refreshSourceList()

				// Return the response, data, and entity
				return { response, data, entity }
			},
		},
	},
)
