import { ref } from 'vue'
import { defineStore } from 'pinia'
import { Log, TLog } from '../../entities/index.js'
import { MissingParameterError, ValidationError } from '../../services/errors/index.js'

const apiEndpoint = '/index.php/apps/openconnector/api/logs'

export const useLogStore = defineStore('log', () => {
	// state
	const logItem = ref<Log>(null)
	const logList = ref<Log[]>([])
	const activeLogKey = ref<string>(null)
	const viewLogItem = ref<Record<string, unknown>>(null)

	// ################################
	// ||    Setters and Getters     ||
	// ################################

	// Log Item
	/**
	 * Set the active log item.
	 * @param item - The log item to set
	 */
	const setLogItem = (item: Log | TLog) => {
		logItem.value = item && new Log(item)
		console.info('Active log item set to ' + item.id)
	}

	/**
	 * Get the active log item.
	 *
	 * @description
	 * Returns the currently active log item. Note that the return value is non-reactive.
	 *
	 * For reactive usage, either:
	 * 1. Reference the `logItem` state directly:
	 * ```js
	 * const logItem = useLogStore().logItem // reactive state
	 * ```
	 * 2. Or wrap in a `computed` property:
	 * ```js
	 * const logItem = computed(() => useLogStore().getLogItem())
	 * ```
	 *
	 * @return {Log | null} The active log item
	 */
	const getLogItem = (): Log | null => logItem.value as Log | null

	/**
	 * Set the active log list.
	 * @param list - The log list to set
	 */
	const setLogList = (list: Log[] | TLog[]) => {
		logList.value = list.map((item) => new Log(item))
		console.info('Log list set to ' + list.length + ' items')
	}

	/**
	 * Get the active log list.
	 *
	 * @description
	 * Returns the currently active log list. Note that the return value is non-reactive.
	 *
	 * For reactive usage, either:
	 * 1. Reference the `logList` state directly:
	 * ```js
	 * const logList = useLogStore().logList // reactive state
	 * ```
	 * 2. Or wrap in a `computed` property:
	 * ```js
	 * const logList = computed(() => useLogStore().getLogList())
	 * ```
	 *
	 * @return {Log[]} The active log list
	 */
	const getLogList = (): Log[] => logList.value as Log[]

	/**
	 * Set the active active log key.
	 * @param key - The log key to set
	 */
	const setActiveLogKey = (key: string) => {
		activeLogKey.value = key
		console.info('Active log key set to ' + key)
	}

	/**
	 * Get the active log key.
	 *
	 * @description
	 * Returns the currently active log key. Note that the return value is non-reactive.
	 *
	 * For reactive usage, either:
	 * 1. Reference the `activeLogKey` state directly:
	 * ```js
	 * const activeLogKey = useLogStore().activeLogKey // reactive state
	 * ```
	 * 2. Or wrap in a `computed` property:
	 * ```js
	 * const activeLogKey = computed(() => useLogStore().getActiveLogKey())
	 * ```
	 *
	 * @return {string | null} The active log key
	 */
	const getActiveLogKey = (): string | null => activeLogKey.value as string | null

	/**
	 * Set the active view log item.
	 * @param item - The log item to set
	 */
	const setViewLogItem = (item: Record<string, unknown>) => {
		viewLogItem.value = item
		console.info('Active view log item set to ' + item.id)
	}

	/**
	 * Get the active view log item.
	 *
	 * @description
	 * Returns the currently active view log item. Note that the return value is non-reactive.
	 *
	 * For reactive usage, either:
	 * 1. Reference the `viewLogItem` state directly:
	 * ```js
	 * const viewLogItem = useLogStore().viewLogItem // reactive state
	 * ```
	 * 2. Or wrap in a `computed` property:
	 * ```js
	 * const viewLogItem = computed(() => useLogStore().getViewLogItem())
	 * ```
	 *
	 * @return {Record<string, unknown>} The active view log item
	 */
	const getViewLogItem = (): Record<string, unknown> => viewLogItem.value as Record<string, unknown>

	// ################################
	// ||          Actions           ||
	// ################################

	// Log
	/**
	 * Refresh the log list
	 * @param search - The search string to filter the list
	 * @return {Promise<{ response: Response, data: TLog[], entities: Log[] }>} The response, data, and entities
	 */
	const refreshLogList = async (search: string = null): Promise<{ response: Response, data: TLog[], entities: Log[] }> => {
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

		const data = (await response.json()).results as TLog[]
		const entities = data.map(logItem => new Log(logItem))

		setLogList(data)

		return { response, data, entities }
	}

	/**
	 * Fetch a single log
	 * @param id - The ID of the log to fetch
	 * @return {Promise<{ response: Response, data: TLog, entity: Log }>} The response, data, and entity
	 */
	const fetchLog = async (id: string): Promise<{ response: Response, data: TLog, entity: Log }> => {
		if (!id) {
			throw new MissingParameterError('id')
		}

		const endpoint = `${apiEndpoint}/${id}`

		const response = await fetch(endpoint, {
			method: 'GET',
		})

		const data = await response.json() as TLog
		const entity = new Log(data)

		setLogItem(data)

		return { response, data, entity }
	}

	/**
	 * Delete a log
	 * @param id - The ID of the log to delete
	 * @return {Promise<{ response: Response }>} The response
	 */
	const deleteLog = async (id: string): Promise<{ response: Response }> => {
		if (!id) {
			throw new MissingParameterError('id')
		}

		console.info('Deleting log...')

		const endpoint = `${apiEndpoint}/${id}`

		const response = await fetch(endpoint, {
			method: 'DELETE',
		})

		response.ok && setLogItem(null)
		refreshLogList()

		return { response }
	}

	/**
	 * Save a log
	 * @param logItem - The log item to save
	 * @return {Promise<{ response: Response, data: TLog, entity: Log }>} The response, data, and entity
	 */
	const saveLog = async (logItem: Log): Promise<{ response: Response, data: TLog, entity: Log }> => {
		if (!logItem) {
			throw new MissingParameterError('logItem')
		}
		if (!(logItem instanceof Log)) {
			throw new Error('logItem is not an instance of Log')
		}

		// verify data with Zod
		const validationResult = logItem.validate()
		if (!validationResult.success) {
			console.error(validationResult.error)
			console.info(logItem)
			throw new ValidationError(validationResult.error)
		}

		console.info('Saving log...')

		const isNewLog = !logItem.id
		const endpoint = isNewLog
			? apiEndpoint
			: `${apiEndpoint}/${logItem.id}`
		const method = isNewLog ? 'POST' : 'PUT'

		const response = await fetch(
			endpoint,
			{
				method,
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify(logItem),
			},
		)

		const data = await response.json() as TLog
		const entity = new Log(data)

		setLogItem(data)
		refreshLogList()

		return { response, data, entity }
	}

	return {
		// state
		logItem,
		logList,
		activeLogKey,
		viewLogItem,

		// setters and getters
		setLogItem,
		getLogItem,
		setLogList,
		getLogList,
		setActiveLogKey,
		getActiveLogKey,
		setViewLogItem,
		getViewLogItem,

		// actions
		refreshLogList,
		fetchLog,
		deleteLog,
		saveLog,
	}
})
