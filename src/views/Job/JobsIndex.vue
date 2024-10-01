<script setup>
import { jobStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<JobsList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!jobStore.jobItem || navigationStore.selected != 'jobs'"
				class="detailContainer"
				name="No job"
				description="No job selected">
				<template #icon>
					<Update />
				</template>
				<template #action>
					<NcButton type="primary" @click="jobStore.setJobItem(null); navigationStore.setModal('editJob')">
						Add job
					</NcButton>
				</template>
			</NcEmptyContent>
			<JobDetails v-if="jobStore.jobItem && navigationStore.selected === 'jobs'" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import JobsList from './JobsList.vue'
import JobDetails from './JobDetails.vue'
import Update from 'vue-material-design-icons/Update.vue'

export default {
	name: 'JobsIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		JobsList,
		JobDetails,
		Update,
	},
}
</script>
