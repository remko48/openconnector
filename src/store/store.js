/* eslint-disable no-console */
// The store script handles app wide variables (or state), for the use of these variables and their governing concepts read the design.md
import pinia from '../pinia.js'
import { useNavigationStore } from './modules/navigation.js'
import { useSearchStore } from './modules/search.js'
import { useJobStore } from './modules/job.js'
import { useLogStore } from './modules/log.js'
import { useMappingStore } from './modules/mapping.js'
import { useSourceStore } from './modules/source.js'
import { useSynchronizationStore } from './modules/synchronization.js'

const navigationStore = useNavigationStore(pinia)
const searchStore = useSearchStore(pinia)
const jobStore = useJobStore(pinia)
const logStore = useLogStore(pinia)
const mappingStore = useMappingStore(pinia)
const sourceStore = useSourceStore(pinia)
const synchronizationStore = useSynchronizationStore(pinia)

export {
	// generic
	navigationStore,
	searchStore,
	// entity-specific
	jobStore,
	logStore,
	mappingStore,
	sourceStore,
	synchronizationStore,
}
