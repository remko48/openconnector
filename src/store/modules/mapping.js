import { defineStore } from 'pinia'
import { Mapping } from '../../entities/index.js'
import { importExportStore } from '../../store/store.js'

export const useMappingStore = defineStore('mapping', {
	state: () => ({
		mappingItem: false,
		mappingList: [],
		mappingMappingKey: null,
		mappingCastKey: null,
		mappingUnsetKey: null,
	}),
	actions: {
		setMappingItem(mappingItem) {
			this.mappingItem = mappingItem && new Mapping(mappingItem)
			console.info('Active mapping item set to ' + mappingItem)
		},
		setMappingList(mappingList) {
			this.mappingList = mappingList.map(
				(mappingItem) => new Mapping(mappingItem),
			)
			console.info('Mapping list set to ' + mappingList.length + ' items')
		},
		setMappingMappingKey(mappingMappingKey) {
			this.mappingMappingKey = mappingMappingKey
			console.info('Active mapping mapping key set to ' + mappingMappingKey)
		},
		setMappingCastKey(mappingCastKey) {
			this.mappingCastKey = mappingCastKey
			console.info('Active mapping cast key set to ' + mappingCastKey)
		},
		setMappingUnsetKey(mappingUnsetKey) {
			this.mappingUnsetKey = mappingUnsetKey
			console.info('Active mapping unset key set to ' + mappingUnsetKey)
		},
		/* istanbul ignore next */ // ignore this for Jest until moved into a service
		async refreshMappingList(search = null) {
			// @todo this might belong in a service?
			let endpoint = '/index.php/apps/openconnector/api/mappings'
			if (search !== null && search !== '') {
				endpoint = endpoint + '?_search=' + search
			}

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			const data = (await response.json()).results
			const entities = data.map(mapping => new Mapping(mapping))

			this.setMappingList(entities)

			return { response, entities }
		},
		// New function to get a single mapping
		async getMapping(id) {
			const endpoint = `/index.php/apps/openconnector/api/mappings/${id}`
			try {
				const response = await fetch(endpoint, {
					method: 'GET',
				})
				const data = await response.json()
				this.setMappingItem(data)
				return data
			} catch (err) {
				console.error(err)
				throw err
			}
		},
		// Delete a mapping
		deleteMapping() {
			if (!this.mappingItem || !this.mappingItem.id) {
				throw new Error('No mapping item to delete')
			}

			console.info('Deleting mapping...')

			const endpoint = `/index.php/apps/openconnector/api/mappings/${this.mappingItem.id}`

			return fetch(endpoint, {
				method: 'DELETE',
			})
				.then((response) => {
					this.refreshMappingList()
				})
				.catch((err) => {
					console.error('Error deleting mapping:', err)
					throw err
				})
		},
		// Create or save a mapping from store
		async saveMapping(mappingItem) {
			if (!mappingItem) {
				throw new Error('No mapping item to save')
			}

			console.info('Saving mapping...')

			const isNewMapping = !mappingItem.id
			const endpoint = isNewMapping
				? '/index.php/apps/openconnector/api/mappings'
				: `/index.php/apps/openconnector/api/mappings/${mappingItem.id}`
			const method = isNewMapping ? 'POST' : 'PUT'

			const response = await fetch(
				endpoint,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(mappingItem),
				},
			)

			const data = await response.json()
			const entity = new Mapping(data)

			this.setMappingItem(entity)
			this.refreshMappingList()

			return { response, data, entity }
		},
		/**
		 * Test a mapping with the provided test object.
		 *
		 * @param {object} mappingTestObject - The object containing the mapping test data.
		 * @param {object} mappingTestObject.inputObject - The input object to test the mapping with.
		 * @param {object} mappingTestObject.mapping - The mapping to be tested.
		 * @param {object} mappingTestObject.schema - (optional) The schema to be used for the test.
		 * @throws Will throw an error if mappingTestObject, inputObject, or mapping is not provided.
		 */
		async testMapping(mappingTestObject) {
			if (!mappingTestObject) {
				throw new Error('No mapping item provided')
			}
			if (!mappingTestObject?.inputObject) {
				throw new Error('No input object to test')
			}
			if (!mappingTestObject?.mapping) {
				throw new Error('No mapping provided')
			}

			// remove unrelated properties
			mappingTestObject = {
				inputObject: mappingTestObject.inputObject,
				mapping: mappingTestObject.mapping,
				schema: mappingTestObject?.schema || null,
				validation: !!mappingTestObject?.schema,
			}

			// assert that the data is an object
			if (typeof mappingTestObject.mapping !== 'object') {
				mappingTestObject.mapping = JSON.parse(mappingTestObject.mapping)
			}
			if (typeof mappingTestObject.inputObject !== 'object') {
				mappingTestObject.inputObject = JSON.parse(mappingTestObject.inputObject)
			}
			if (!!mappingTestObject.schema && typeof mappingTestObject.schema !== 'object') {
				mappingTestObject.schema = JSON.parse(mappingTestObject.schema)
			}

			console.info('Testing mapping...')

			const response = await fetch(
				'/index.php/apps/openconnector/api/mappings/test',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(mappingTestObject),
				},
			)

			const data = await response.json()

			return { response, data }
		},
		/**
		 * Get objects on a mapping from the endpoint.
		 *
		 * This method fetches objects related to a mapping from the specified API endpoint.
		 *
		 * @throws Will throw an error if the fetch operation fails.
		 * @return { Promise<{ response: Response, data: object }> } The response and data from the API.
		 */
		async getMappingObjects() {
			console.info('Fetching mapping objects...')

			// Fetch objects related to a mapping from the API endpoint
			const response = await fetch(
				'/index.php/apps/openconnector/api/mappings/objects',
				{
					method: 'GET',
					headers: {
						'Content-Type': 'application/json',
					},
				},
			)

			// Parse the response data as JSON
			const data = await response.json()

			// Return the response and parsed data
			return { response, data }
		},
		/**
		 * Save a mapping object to the endpoint.
		 *
		 * This method sends a mapping object to the specified API endpoint to be saved.
		 *
		 * @param { object } mappingObject - The mapping object to be saved.
		 * @return { Promise<{ response: Response, data: object }> } The response and data from the API.
		 * @throws Will throw an error if the save operation fails.
		 */
		async saveMappingObject(mappingObject) {
			console.info('Saving mapping object...')

			// Send the mapping object to the API endpoint to be saved
			const response = await fetch(
				'/index.php/apps/openconnector/api/mappings/objects',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(mappingObject),
				},
			)

			// Parse the response data as JSON
			const data = await response.json()

			// Return the response and parsed data
			return { response, data }
		},
		// Export a mapping
		exportMapping() {
			if (!this.mappingItem) {
				throw new Error('No mapping item to export')
			}
			importExportStore.exportFile(
				this.mappingItem.id,
				this.mappingItem.name,
				'mapping',
			)
				.then(({ download }) => {
					download()
				})
				.catch((err) => {
					console.error('Error exporting mapping:', err)
					throw err
				})
		},
	},
})
