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
				console.log('Active log item set to ' + logItem && logItem?.id)
			},
			setLogList(logList) {
				this.logList = logList.map(
					(logItem) => new Log(logItem),
				)
				console.log('Log list set to ' + logList.length + ' items')
			},
			// ... other actions (refreshLogList, deleteLog, saveLog) ...
		},
	},
)