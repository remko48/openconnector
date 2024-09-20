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
				name="Geen taak"
				description="Nog geen taak geselecteerd">
				<template #icon>
					<Briefcase />
				</template>
				<template #action>
					<NcButton type="primary" @click="jobStore.setJobItem({}); navigationStore.setModal('editJob')">
						Taak toevoegen
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
import Briefcase from 'vue-material-design-icons/Briefcase.vue'

export default {
	name: 'JobsIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		JobsList,
		JobDetails,
		Briefcase,
	},
}
</script>
