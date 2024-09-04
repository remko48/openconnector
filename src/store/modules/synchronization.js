/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Synchronization } from '../../entities/index.js'

export const useSynchronizationStore = defineStore(
	'synchronization', {
		state: () => ({
			synchronizationItem: false,
			synchronizationList: [],
		}),
		actions: {
			setSynchronizationItem(synchronizationItem) {
				this.synchronizationItem = synchronizationItem && new Synchronization(synchronizationItem)
				console.log('Active synchronization item set to ' + synchronizationItem && synchronizationItem?.id)
			},
			setSynchronizationList(synchronizationList) {
				this.synchronizationList = synchronizationList.map(
					(synchronizationItem) => new Synchronization(synchronizationItem),
				)
				console.log('Synchronization list set to ' + synchronizationList.length + ' items')
			},
			// ... other actions (refreshSynchronizationList, deleteSynchronization, saveSynchronization) ...
		},
	},
)