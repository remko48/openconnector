/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Source } from '../../entities/index.js'

export const useSourceStore = defineStore(
	'source', {
		state: () => ({
			sourceItem: false,
			sourceList: [],
		}),
		actions: {
			setSourceItem(sourceItem) {
				this.sourceItem = sourceItem && new Source(sourceItem)
				console.log('Active source item set to ' + sourceItem && sourceItem?.id)
			},
			setSourceList(sourceList) {
				this.sourceList = sourceList.map(
					(sourceItem) => new Source(sourceItem),
				)
				console.log('Source list set to ' + sourceList.length + ' items')
			},
			/* istanbul ignore next */ // ignore this for Jest until moved into a service
			async refreshSourceList(search = null) {
				// @todo this might belong in a service?
				let endpoint = '/index.php/apps/larpingapp/api/sources'
				if (search !== null && search !== '') {
					endpoint = endpoint + '?_search=' + search
				}
				return fetch(endpoint, {
					method: 'GET',
				})
					.then((response) => {
						response.json().then((data) => {
							this.setSourceList(data.results)
						})
					})
					.catch((err) => {
						console.error(err)
					})
			},
			deleteSource() {
				if (!this.sourceItem || !this.sourceItem.id) {
					throw new Error('No source item to delete')
				}

				console.log('Deleting source...')

				const endpoint = `/index.php/apps/larpingapp/api/sources/${this.sourceItem.id}`

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

			saveSource() {
				if (!this.sourceItem) {
					throw new Error('No source item to save')
				}

				console.log('Saving source...')

				const isNewSource = !this.sourceItem.id
				const endpoint = isNewSource
					? '/index.php/apps/larpingapp/api/sources'
					: `/index.php/apps/larpingapp/api/sources/${this.sourceItem.id}`
				const method = isNewSource ? 'POST' : 'PUT'

				return fetch(
					endpoint,
					{
						method: method,
						headers: {
							'Content-Type': 'application/json',
						},
						body: JSON.stringify(this.sourceItem),
					},
				)
					.then((response) => response.json())
					.then((data) => {
						this.setSourceItem(data)
						console.log('Source saved')
						return this.refreshSourceList()
					})
					.catch((err) => {
						console.error('Error saving source:', err)
						throw err
					})
			},
		},
	},
)