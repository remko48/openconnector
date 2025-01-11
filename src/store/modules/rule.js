/**
 * @fileoverview Event store module for managing event-related state and actions
 */

import { defineStore } from 'pinia'
import { Event } from '../../entities/index.js'

/**
 * Event store definition using Pinia
 * @returns {Object} Store instance with state and actions
 */
export const useEventStore = defineStore('event', {
	state: () => ({
		/** @type {Event|false} Current active event */
		eventItem: false,
		/** @type {Event[]} List of events */
		eventList: [],
		/** @type {Object|false} Event test results */
		eventTest: false,
		/** @type {Object|false} Event run results */
		eventRun: false,
		/** @type {Array} Event logs */
		eventLogs: [],
	}),
	actions: {
		/**
		 * Sets the current active event
		 * @param {Object} eventItem - Event data to set
		 * @returns {void}
		 */
		setEventItem(eventItem) {
			this.eventItem = eventItem && new Event(eventItem)
			console.log('Active event item set to ' + eventItem)
		},

		/**
		 * Sets the list of events
		 * @param {Array} eventList - Array of event data
		 * @returns {void}
		 */
		setEventList(eventList) {
			this.eventList = eventList.map(
				(eventItem) => new Event(eventItem),
			)
			console.log('Event list set to ' + eventList.length + ' items')
		},

		/**
		 * Refreshes the event list from the API
		 * @param {string|null} search - Optional search query
		 * @returns {Promise} Fetch promise
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
				console.error(err)
				throw err
			}
		},

		/**
		 * Gets a single event by ID
		 * @param {string} id - Event ID
		 * @returns {Promise} Fetch promise
		 */
		async getEvent(id) {
			const endpoint = `/index.php/apps/openconnector/api/events/${id}`
			try {
				const response = await fetch(endpoint)
				const data = await response.json()
				this.setEventItem(data)
				return data
			} catch (err) {
				console.error(err)
				throw err
			}
		},

		/**
		 * Saves or creates an event
		 * @param {Object} eventItem - Event data to save
		 * @returns {Promise} Fetch promise
		 */
		async saveEvent(eventItem) {
			if (!eventItem) {
				throw new Error('No event item to save')
			}

			const isNewEvent = !eventItem.id
			const endpoint = isNewEvent
				? '/index.php/apps/openconnector/api/events'
				: `/index.php/apps/openconnector/api/events/${eventItem.id}`
			const method = isNewEvent ? 'POST' : 'PUT'

			// Clean up the event data before saving
			const eventToSave = { ...eventItem }
			Object.keys(eventToSave).forEach(key => {
				if (eventToSave[key] === '' || 
					(Array.isArray(eventToSave[key]) && !eventToSave[key].length) || 
					key === 'created' || 
					key === 'updated') {
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
