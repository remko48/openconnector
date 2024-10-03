/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Log } from '../../entities/index.js'

export const useLogStore = defineStore(
	'log', {
		state: () => ({
			logItem: false,
			logList: [],
		}),
		actions: {
			setLogItem(logItem) {
				this.logItem = logItem && new Log(logItem)
				console.log('Active log item set to ' + logItem)
			},
			setLogList(logList) {
				this.logList = logList.map(
					(logItem) => new Log(logItem),
				)
				console.log('Log list set to ' + logList.length + ' items')
			},
			/* istanbul ignore next */ // ignore this for Jest until moved into a service
			async refreshLogList(search = null) {
				// @todo this might belong in a service?
				let endpoint = '/index.php/apps/openconnector/api/logs'
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
									this.setLogList(data.results)
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
			// New function to get a single log
			async getLog(id) {
				const endpoint = `/index.php/apps/openconnector/api/logs/${id}`
				try {
					const response = await fetch(endpoint, {
						method: 'GET',
					})
					const data = await response.json()
					this.setLogItem(data)
					return data
				} catch (err) {
					console.error(err)
					throw err
				}
			},
			// Delete a log
			deleteLog() {
				if (!this.logItem || !this.logItem.id) {
					throw new Error('No log item to delete')
				}

				console.log('Deleting log...')

				const endpoint = `/index.php/apps/openconnector/api/logs/${this.logItem.id}`

				return fetch(endpoint, {
					method: 'DELETE',
				})
					.then((response) => {
						this.refreshLogList()
					})
					.catch((err) => {
						console.error('Error deleting log:', err)
						throw err
					})
			},
			// Create or save a log from store
			saveLog() {
				if (!this.logItem) {
					throw new Error('No log item to save')
				}

				console.log('Saving log...')

				const isNewLog = !this.logItem.id
				const endpoint = isNewLog
					? '/index.php/apps/openconnector/api/logs'
					: `/index.php/apps/openconnector/api/logs/${this.logItem.id}`
				const method = isNewLog ? 'POST' : 'PUT'

				// Create a copy of the log item and remove empty properties
				const logToSave = { ...this.logItem }
				Object.keys(logToSave).forEach(key => {
					if (logToSave[key] === '' || (Array.isArray(logToSave[key]) && !logToSave[key].length)) {
						delete logToSave[key]
					}
				})

				return fetch(
					endpoint,
					{
						method,
						headers: {
							'Content-Type': 'application/json',
						},
						body: JSON.stringify(logToSave),
					},
				)
					.then((response) => response.json())
					.then((data) => {
						this.setLogItem(data)
						console.log('Log saved')
						// Refresh the log list
						return this.refreshLogList()
					})
					.catch((err) => {
						console.error('Error saving log:', err)
						throw err
					})
			},
		},
	},
)
