/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Mapping } from '../../entities/index.js'

export const useMappingStore = defineStore(
	'mapping', {
		state: () => ({
			mappingItem: false,
			mappingList: [],
		}),
		actions: {
			setMappingItem(mappingItem) {
				this.mappingItem = mappingItem && new Mapping(mappingItem)
				console.log('Active mapping item set to ' + mappingItem && mappingItem?.id)
			},
			setMappingList(mappingList) {
				this.mappingList = mappingList.map(
					(mappingItem) => new Mapping(mappingItem),
				)
				console.log('Mapping list set to ' + mappingList.length + ' items')
			},
			// ... other actions (refreshMappingList, deleteMapping, saveMapping) ...
		},
	},
)