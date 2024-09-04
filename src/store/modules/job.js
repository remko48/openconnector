/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Job } from '../../entities/index.js'

export const useJobStore = defineStore(
	'job', {
		state: () => ({
			jobItem: false,
			jobList: [],
		}),
		actions: {
			setJobItem(jobItem) {
				this.jobItem = jobItem && new Job(jobItem)
				console.log('Active job item set to ' + jobItem && jobItem?.id)
			},
			setJobList(jobList) {
				this.jobList = jobList.map(
					(jobItem) => new Job(jobItem),
				)
				console.log('Job list set to ' + jobList.length + ' items')
			},
			// ... other actions (refreshJobList, deleteJob, saveJob) ...
		},
	},
)