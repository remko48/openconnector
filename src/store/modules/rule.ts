import { ref } from 'vue'
import { defineStore } from 'pinia'
import { importExportStore } from '../store.js'
import { Rule, TRule } from '../../entities/index.js'
import { MissingParameterError } from '../../services/errors/index.js'

const apiEndpoint = '/index.php/apps/openconnector/api/rules'

export const useRuleStore = defineStore('rule', () => {
	// state
	const ruleItem = ref<Rule>(null)
	const ruleList = ref<Rule[]>([])
	const ruleTest = ref<object>(null)
	const ruleRun = ref<object>(null)
	const ruleLogs = ref<object[]>([])

	// ################################
	// ||    Setters and Getters     ||
	// ################################

	// Rule Item
	/**
	 * Set the active rule item.
	 * @param item - The rule item to set
	 */
	const setRuleItem = (item: Rule | TRule) => {
		ruleItem.value = item && new Rule(item)
		console.info('Active rule item set to ' + (item ? item.id : 'null'))
	}

	/**
	 * Get the active rule item.
	 *
	 * @description
	 * Returns the currently active rule item. Note that the return value is non-reactive.
	 *
	 * For reactive usage, either:
	 * 1. Reference the `ruleItem` state directly:
	 * ```js
	 * const ruleItem = useRuleStore().ruleItem // reactive state
	 * ```
	 * 2. Or wrap in a `computed` property:
	 * ```js
	 * const ruleItem = computed(() => useRuleStore().getRuleItem())
	 * ```
	 *
	 * @return {Rule | null} The active rule item
	 */
	const getRuleItem = (): Rule | null => ruleItem.value as Rule | null

	/**
	 * Set the active rule list.
	 * @param list - The rule list to set
	 */
	const setRuleList = (list: Rule[] | TRule[]) => {
		ruleList.value = list.map((item) => new Rule(item))
		console.info('Rule list set to ' + list.length + ' items')
	}

	/**
	 * Get the active rule list.
	 *
	 * @description
	 * Returns the currently active rule list. Note that the return value is non-reactive.
	 *
	 * For reactive usage, either:
	 * 1. Reference the `ruleList` state directly:
	 * ```js
	 * const ruleList = useRuleStore().ruleList // reactive state
	 * ```
	 * 2. Or wrap in a `computed` property:
	 * ```js
	 * const ruleList = computed(() => useRuleStore().getRuleList())
	 * ```
	 *
	 * @return {Rule[]} The active rule list
	 */
	const getRuleList = (): Rule[] => ruleList.value as Rule[]

	/**
	 * Set the active rule test results.
	 * @param item - The rule test results to set
	 */
	const setRuleTest = (item: object) => {
		ruleTest.value = item
		console.info('Active rule test results set to ' + item)
	}

	/**
	 * Get the active rule test results.
	 *
	 * @description
	 * Returns the currently active rule test results. Note that the return value is non-reactive.
	 *
	 * For reactive usage, either:
	 * 1. Reference the `ruleTest` state directly:
	 * ```js
	 * const ruleTest = useRuleStore().ruleTest // reactive state
	 * ```
	 * 2. Or wrap in a `computed` property:
	 * ```js
	 * const ruleTest = computed(() => useRuleStore().getRuleTest())
	 * ```
	 *
	 * @return {object | null} The active rule test results
	 */
	const getRuleTest = (): object | null => ruleTest.value as object | null

	/**
	 * Set the active rule item.
	 * @param item - The rule item to set
	 */
	const setRuleRun = (item: object) => {
		ruleRun.value = item
		console.info('Active rule run results set to ' + item)
	}

	/**
	 * Get the active rule run results.
	 *
	 * @description
	 * Returns the currently active rule run results. Note that the return value is non-reactive.
	 *
	 * For reactive usage, either:
	 * 1. Reference the `ruleRun` state directly:
	 * ```js
	 * const ruleRun = useRuleStore().ruleRun // reactive state
	 * ```
	 * 2. Or wrap in a `computed` property:
	 * ```js
	 * const ruleRun = computed(() => useRuleStore().getRuleRun())
	 * ```
	 *
	 * @return {object | null} The active rule run results
	 */
	const getRuleRun = (): object | null => ruleRun.value as object | null

	/**
	 * Set the active rule logs.
	 * @param item - The rule logs to set
	 */
	const setRuleLogs = (item: object[]) => {
		ruleLogs.value = item
		console.info('Active rule logs set to ' + item.length + ' items')
	}

	/**
	 * Get the active rule logs.
	 *
	 * @description
	 * Returns the currently active rule logs. Note that the return value is non-reactive.
	 *
	 * For reactive usage, either:
	 * 1. Reference the `ruleLogs` state directly:
	 * ```js
	 * const ruleLogs = useRuleStore().ruleLogs // reactive state
	 * ```
	 * 2. Or wrap in a `computed` property:
	 * ```js
	 * const ruleLogs = computed(() => useRuleStore().getRuleLogs())
	 * ```
	 *
	 * @return {object[]} The active rule logs
	 */
	const getRuleLogs = (): object[] => ruleLogs.value as object[]

	// ################################
	// ||          Actions           ||
	// ################################

	/**
	 * Refresh the rule list
	 * @param search - The search string to filter the list
	 * @return {Promise<{ response: Response, data: TRule[], entities: Rule[] }>} The response, data, and entities
	 */
	const refreshRuleList = async (search: string = null): Promise<{ response: Response, data: TRule[], entities: Rule[] }> => {
		const queryParams = new URLSearchParams()

		if (search !== null && search !== '') {
			queryParams.append('_search', search)
		}

		// Build the endpoint with query params if they exist
		let endpoint = apiEndpoint
		if (queryParams.toString()) {
			endpoint += '?' + queryParams.toString()
		}

		const response = await fetch(endpoint, {
			method: 'GET',
		})

		const data = (await response.json()).results as TRule[]
		const entities = data.map(ruleItem => new Rule(ruleItem))

		setRuleList(data)

		return { response, data, entities }
	}

	/**
	 * Fetch a single rule
	 * @param id - The ID of the rule to fetch
	 * @return {Promise<{ response: Response, data: TRule, entity: Rule }>} The response, data, and entity
	 */
	const fetchRule = async (id: string): Promise<{ response: Response, data: TRule, entity: Rule }> => {
		if (!id) {
			throw new MissingParameterError('id')
		}

		const endpoint = `${apiEndpoint}/${id}`

		const response = await fetch(endpoint, {
			method: 'GET',
		})

		const data = await response.json() as TRule
		const entity = new Rule(data)

		setRuleItem(data)

		return { response, data, entity }
	}

	/**
	 * Delete a rule
	 * @param id - The ID of the rule to delete
	 * @return {Promise<{ response: Response }>} The response
	 */
	const deleteRule = async (id: string): Promise<{ response: Response }> => {
		if (!id) {
			throw new MissingParameterError('id')
		}

		console.info('Deleting rule...')

		const endpoint = `${apiEndpoint}/${id}`

		const response = await fetch(endpoint, {
			method: 'DELETE',
		})

		response.ok && setRuleItem(null)
		refreshRuleList()

		return { response }
	}

	/**
	 * Save a rule
	 * @param ruleItem - The rule item to save
	 * @return {Promise<{ response: Response, data: TRule, entity: Rule }>} The response, data, and entity
	 */
	const saveRule = async (ruleItem: Rule): Promise<{ response: Response, data: TRule, entity: Rule }> => {
		if (!ruleItem) {
			throw new MissingParameterError('ruleItem')
		}
		if (!(ruleItem instanceof Rule)) {
			throw new Error('ruleItem is not an instance of Rule')
		}

		// DISABLED UNTIL TIME CAN BE SPENT TO DO VALIDATION PROPERLY
		// verify data with Zod
		// const validationResult = ruleItem.validate()
		// if (!validationResult.success) {
		//  console.error(validationResult.error)
		//  console.info(ruleItem)
		//  throw new ValidationError(validationResult.error)
		// }

		// delete "updated"
		const clonedRule = ruleItem.cloneRaw()
		delete clonedRule.updated
		ruleItem = new Rule(clonedRule)

		console.info('Saving rule...')

		const isNewRule = !ruleItem.id
		const endpoint = isNewRule
			? apiEndpoint
			: `${apiEndpoint}/${ruleItem.id}`
		const method = isNewRule ? 'POST' : 'PUT'

		const response = await fetch(
			endpoint,
			{
				method,
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify(ruleItem),
			},
		)

		const data = await response.json() as TRule
		const entity = new Rule(data)

		setRuleItem(data)
		refreshRuleList()

		return { response, data, entity }
	}

	// Export a rule
	const exportRule = async (id: string) => {
		if (!id) {
			throw new Error('No rule item to export')
		}
		importExportStore.exportFile(
			id,
			'rule',
		)
			.then(({ download }) => {
				download()
			})
			.catch((err) => {
				console.error('Error exporting rule:', err)
				throw err
			})
	}

	return {
		// state
		ruleItem,
		ruleList,
		ruleTest,
		ruleRun,
		ruleLogs,

		// setters and getters
		setRuleItem,
		getRuleItem,
		setRuleList,
		getRuleList,
		setRuleTest,
		getRuleTest,
		setRuleRun,
		getRuleRun,
		setRuleLogs,
		getRuleLogs,

		// actions
		refreshRuleList,
		fetchRule,
		deleteRule,
		saveRule,
		exportRule,
	}
})
