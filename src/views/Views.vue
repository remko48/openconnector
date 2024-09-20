<script setup>
import { navigationStore } from '../store/store.js'
</script>

<template>
	<!-- Placeholder div for all of the views-->
	<NcAppContent>
		<template #default>
			<Dashboard v-if="navigationStore.selected === 'dashboard'" />
			<SourcesIndex v-if="navigationStore.selected === 'sources'" />
			<JobsIndex v-if="navigationStore.selected === 'jobs'" />
			<LogsIndex v-if="navigationStore.selected === 'logs'" />
			<MappingsIndex v-if="navigationStore.selected === 'mappings'" />
			<SynchronizationsIndex v-if="navigationStore.selected === 'synchronizations'" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent } from '@nextcloud/vue'
import Dashboard from './dashboard/DashboardIndex.vue'
import SourcesIndex from './Source/SourcesIndex.vue'
import JobsIndex from './Job/JobsIndex.vue'
import LogsIndex from './Log/LogsIndex.vue'
import MappingsIndex from './Mapping/MappingsIndex.vue'
import SynchronizationsIndex from './Synchronization/SynchronizationsIndex.vue'

export default {
	name: 'Views',
	components: {
		Dashboard,
		NcAppContent,
		SourcesIndex,
		JobsIndex,
		LogsIndex,
		MappingsIndex,
		SynchronizationsIndex,
	},
}
</script>
