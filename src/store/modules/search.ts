import { ref } from 'vue'
import { defineStore } from 'pinia'

const apiEndpoint = '/index.php/apps/openconnector/api/search'

export const useSearchStore = defineStore('search', () => {
	// state
	const search = ref<string>('')
	const searchResults = ref<object>({})
	const searchError = ref<string>('')

	// ################################
	// ||    Setters and Getters     ||
	// ################################

	/**
	 * Set the active search.
	 * @param item - The search item to set
	 */
	const setSearch = (item: string) => {
		search.value = item
		console.info('Active search set to ' + item)
	}

	/**
	 * Get the active search.
	 *
	 * @description
	 * Returns the currently active search. Note that the return value is non-reactive.
	 *
	 * For reactive usage, either:
	 * 1. Reference the `search` state directly:
	 * ```js
	 * const search = useSearchStore().search // reactive state
	 * ```
	 * 2. Or wrap in a `computed` property:
	 * ```js
	 * const search = computed(() => useSearchStore().getSearch())
	 * ```
	 *
	 * @return {string} The active search
	 */
	const getSearch = (): string => search.value

	/**
	 * Set the active search results.
	 * @param item - The search item to set
	 */
	const setSearchResults = (item: object) => {
		searchResults.value = item
		console.info('Active search results set to ' + item)
	}

	/**
	 * Get the active search results.
	 *
	 * @description
	 * Returns the currently active search results. Note that the return value is non-reactive.
	 *
	 * For reactive usage, either:
	 * 1. Reference the `searchResults` state directly:
	 * ```js
	 * const searchResults = useSearchStore().searchResults // reactive state
	 * ```
	 * 2. Or wrap in a `computed` property:
	 * ```js
	 * const searchResults = computed(() => useSearchStore().getSearchResults())
	 * ```
	 *
	 * @return {object} The active search results
	 */
	const getSearchResults = (): object => searchResults.value

	/**
	 * Set the active search error.
	 * @param item - The search error to set
	 */
	const setSearchError = (item: string) => {
		searchError.value = item
		console.info('Active search error set to ' + item)
	}

	/**
	 * Get the active search error.
	 *
	 * @description
	 * Returns the currently active search error. Note that the return value is non-reactive.
	 *
	 * For reactive usage, either:
	 * 1. Reference the `searchError` state directly:
	 * ```js
	 * const searchError = useSearchStore().searchError // reactive state
	 * ```
	 * 2. Or wrap in a `computed` property:
	 * ```js
	 * const searchError = computed(() => useSearchStore().getSearchError())
	 * ```
	 *
	 * @return {string} The active search error
	 */
	const getSearchError = (): string => searchError.value

	// ################################
	// ||          Actions           ||
	// ################################

	const fetchSearchResults = () => {
		fetch(
			`${apiEndpoint}?_search=${search.value}`,
			{
				method: 'GET',
			},
		)
			.then(
				(response) => {
					response.json().then(
						(data) => {
							if (data?.code === 403 && data?.message) {
								setSearchError(data.message)
								console.info(searchError.value)
							} else {
								setSearchError('') // Clear any previous errors
							}
							setSearchResults(data)
						},
					)
				},
			)
			.catch(
				(err) => {
					setSearchError(err.message || 'An error occurred')
					console.error(err.message ?? err)
				},
			)
	}

	const clearSearch = () => {
		search.value = ''
		searchError.value = ''
		searchResults.value = {}
	}

	return {
		// state
		search,
		searchResults,
		searchError,

		// setters and getters
		setSearch,
		getSearch,
		setSearchResults,
		getSearchResults,
		setSearchError,
		getSearchError,

		// actions
		fetchSearchResults,
		clearSearch,
	}
})
