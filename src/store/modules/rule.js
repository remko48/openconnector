/**
 * @fileoverview Rule store module for managing rule-related state and actions
 */

import { defineStore } from 'pinia'
import { Rule } from '../../entities/index.js'

/**
 * Rule store definition using Pinia
 * @returns {Object} Store instance with state and actions
 */
export const useRuleStore = defineStore('rule', {
	state: () => ({
		/** @type {Rule|false} Current active rule */
		ruleItem: false,
		/** @type {Rule[]} List of rules */
		ruleList: [],
		/** @type {Object|false} Rule test results */
		ruleTest: false,
		/** @type {Object|false} Rule run results */
		ruleRun: false,
		/** @type {Array} Rule logs */
		ruleLogs: [],
	}),
	actions: {
		/**
		 * Sets the current active rule
		 * @param {Object} ruleItem - Rule data to set
		 * @returns {void}
		 */
		setRuleItem(ruleItem) {
			this.ruleItem = ruleItem && new Rule(ruleItem)
			console.log('Active rule item set to ' + ruleItem)
		},

		/**
		 * Sets the list of rules
		 * @param {Array} ruleList - Array of rule data
		 * @returns {void}
		 */
		setRuleList(ruleList) {
			this.ruleList = ruleList.map(
				(ruleItem) => new Rule(ruleItem),
			)
			console.log('Rule list set to ' + ruleList.length + ' items')
		},

		/**
		 * Refreshes the rule list from the API
		 * @param {string|null} search - Optional search query
		 * @returns {Promise} Fetch promise
		 */
		async refreshRuleList(search = null) {
			let endpoint = '/index.php/apps/openconnector/api/rules'
			if (search !== null && search !== '') {
				endpoint = endpoint + '?_search=' + search
			}
			try {
				const response = await fetch(endpoint)
				const data = await response.json()
				this.setRuleList(data.results)
				return data
			} catch (err) {
				console.error(err)
				throw err
			}
		},

		/**
		 * Gets a single rule by ID
		 * @param {string} id - Rule ID
		 * @returns {Promise} Fetch promise
		 */
		async getRule(id) {
			const endpoint = `/index.php/apps/openconnector/api/rules/${id}`
			try {
				const response = await fetch(endpoint)
				const data = await response.json()
				this.setRuleItem(data)
				return data
			} catch (err) {
				console.error(err)
				throw err
			}
		},

		/**
		 * Saves or creates a rule
		 * @param {Object} ruleItem - Rule data to save
		 * @returns {Promise} Fetch promise
		 */
		async saveRule(ruleItem) {
			if (!ruleItem) {
				throw new Error('No rule item to save')
			}

			const isNewRule = !ruleItem.id
			const endpoint = isNewRule
				? '/index.php/apps/openconnector/api/rules'
				: `/index.php/apps/openconnector/api/rules/${ruleItem.id}`
			const method = isNewRule ? 'POST' : 'PUT'

			// Clean up the rule data before saving
			const ruleToSave = { ...ruleItem }
			Object.keys(ruleToSave).forEach(key => {
				if (ruleToSave[key] === '' || 
					(Array.isArray(ruleToSave[key]) && !ruleToSave[key].length) || 
					key === 'created' || 
					key === 'updated') {
					delete ruleToSave[key]
				}
			})

			try {
				const response = await fetch(endpoint, {
					method,
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(ruleToSave),
				})
				const data = await response.json()
				this.setRuleItem(data)
				await this.refreshRuleList()
				return data
			} catch (err) {
				console.error('Error saving rule:', err)
				throw err
			}
		},
	},
})
