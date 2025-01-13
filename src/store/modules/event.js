/**
 * @file Event store module for managing event-related state and actions
 * @module store/modules/event
 */

import { defineStore } from 'pinia'
import { Event } from '../../entities/index.js'

/**
 * Event store definition using Pinia
 * @return {object} Store instance with state and actions
 */
export const useEventStore = defineStore('event', {
	state: () => ({
		/** @type {Event|false} Current active event */
		eventItem: false,
		/** @type {object | false} Event test results */
		eventTest: false,
		/** @type {object | false} Event run results */
		eventRun: false,
		/** @type {Event[]} List of events */
		eventList: [],
		/** @type {object | false} Current event log */
		eventLog: false,
		/** @type {Array} Event logs collection */
		eventLogs: [],
		/** @type {string|null} Current event argument key */
		eventArgumentKey: null,
	}),
	actions: {
		/**
		 * Sets the current active event
		 * @param {object} eventItem - Event data to set
		 * @return {void}
		 */
		setEventItem(eventItem) {
			this.eventItem = eventItem && new Event(eventItem)
			console.log('Active event item set to ' + eventItem)
		},

		/**
		 * Sets the event test status
		 * @param {object} eventTest - Test status to set
		 * @return {void}
		 */
		setEventTest(eventTest) {
			this.eventTest = eventTest
			console.log('Event test set to ' + eventTest)
		},

		/**
		 * Sets the event run status
		 * @param {object} eventRun - Run status to set
		 * @return {void}
		 */
		setEventRun(eventRun) {
			this.eventRun = eventRun
			console.log('Event run set to ' + eventRun)
		},

		/**
		 * Sets the list of events
		 * @param {Array} eventList - Array of event data
		 * @return {void}
		 */
		setEventList(eventList) {
			this.eventList = eventList.map(
				(eventItem) => new Event(eventItem),
			)
			console.log('Event list set to ' + eventList.length + ' items')
		},

		/**
		 * Sets the event logs
		 * @param {Array} eventLogs - Array of event logs
		 * @return {void}
		 */
		setEventLogs(eventLogs) {
			this.eventLogs = eventLogs
			console.log('Event logs set to ' + eventLogs.length + ' items')
		},

		/**
		 * Sets the current event argument key
		 * @param {string} eventArgumentKey - Argument key to set
		 * @return {void}
		 */
		setEventArgumentKey(eventArgumentKey) {
			this.eventArgumentKey = eventArgumentKey
			console.log('Active event argument key set to ' + eventArgumentKey)
		},

		/**
		 * Refreshes the event list from the API
		 * @param {string|null} search - Optional search query
		 * @return {Promise} Fetch promise
		 */
		async refreshEventList(search = null) {
			let endpoint = '/index.php/apps/openconnector/api/events'
			if (search !== null && search !== '') {
				endpoint = endpoint + '?_search=' + search
			}
			try {
				const response = await fetch(endpoint)
				const data = await response.json()
				this.setEventList(data.results)
				return data
			} catch (err) {
				console.error('Error refreshing event list:', err)
				throw err
			}
		},

		/**
		 * Gets a single event by ID
		 * @param {string} id - Event ID
		 * @return {Promise} Fetch promise
		 */
		async getEvent(id) {
			const endpoint = `/index.php/apps/openconnector/api/events/${id}`
			try {
				const response = await fetch(endpoint)
				const data = await response.json()
				this.setEventItem(data)
				return data
			} catch (err) {
				console.error('Error getting event:', err)
				throw err
			}
		},

		/**
		 * Refreshes event logs for the current event
		 * @return {Promise} Fetch promise
		 */
		async refreshEventLogs() {
			if (!this.eventItem?.id) {
				throw new Error('No event item selected')
			}
			const endpoint = `/index.php/apps/openconnector/api/events-logs/${this.eventItem.id}`
			try {
				const response = await fetch(endpoint)
				const data = await response.json()
				this.setEventLogs(data)
				return data
			} catch (err) {
				console.error('Error refreshing event logs:', err)
				throw err
			}
		},

		/**
		 * Deletes the current event
		 * @return {Promise} Fetch promise
		 */
		async deleteEvent() {
			if (!this.eventItem?.id) {
				throw new Error('No event item to delete')
			}

			console.log('Deleting event...')
			const endpoint = `/index.php/apps/openconnector/api/events/${this.eventItem.id}`

			try {
				await fetch(endpoint, { method: 'DELETE' })
				await this.refreshEventList()
			} catch (err) {
				console.error('Error deleting event:', err)
				throw err
			}
		},

		/**
		 * Tests the current event
		 * @return {Promise} Fetch promise
		 */
		async testEvent() {
			if (!this.eventItem?.id) {
				throw new Error('No event item to test')
			}

			console.log('Testing event...')
			const endpoint = `/index.php/apps/openconnector/api/events-test/${this.eventItem.id}`

			try {
				const response = await fetch(endpoint, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify([]),
				})
				const data = await response.json()
				this.setEventTest(data)
				await this.refreshEventLogs()
				return data
			} catch (err) {
				console.error('Error testing event:', err)
				await this.refreshEventLogs()
				throw err
			}
		},

		/**
		 * Runs an event by ID
		 * @param {string} id - Event ID to run
		 * @return {Promise} Fetch promise
		 */
		async runEvent(id) {
			if (!id) {
				throw new Error('No event ID provided to run')
			}

			console.log('Running event...')
			const endpoint = `/index.php/apps/openconnector/api/events-run/${id}`

			try {
				const response = await fetch(endpoint, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify([]),
				})
				const data = await response.json()
				this.setEventRun(data)
				await this.refreshEventLogs()
				return { response, data }
			} catch (err) {
				console.error('Error running event:', err)
				await this.refreshEventLogs()
				throw err
			}
		},

		/**
		 * Saves or creates an event
		 * @param {object} eventItem - Event data to save
		 * @return {Promise} Fetch promise
		 */
		async saveEvent(eventItem) {
			if (!eventItem) {
				throw new Error('No event item to save')
			}

			console.log('Saving event...')
			const isNewEvent = !eventItem.id
			const endpoint = isNewEvent
				? '/index.php/apps/openconnector/api/events'
				: `/index.php/apps/openconnector/api/events/${eventItem.id}`
			const method = isNewEvent ? 'POST' : 'PUT'

			// Clean up the event data before saving
			const eventToSave = { ...eventItem }
			Object.keys(eventToSave).forEach(key => {
				if (eventToSave[key] === ''
					|| (Array.isArray(eventToSave[key]) && !eventToSave[key].length)
					|| key === 'created'
					|| key === 'updated') {
					delete eventToSave[key]
				}
			})

			try {
				const response = await fetch(endpoint, {
					method,
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(eventToSave),
				})
				const data = await response.json()
				this.setEventItem(data)
				await this.refreshEventList()
				return data
			} catch (err) {
				console.error('Error saving event:', err)
				throw err
			}
		},
	},
})
