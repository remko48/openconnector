<script setup>
import { logStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<LogsList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!logStore.logItem || navigationStore.selected != 'logs'"
				class="detailContainer"
				name="Geen log"
				description="Nog geen log geselecteerd">
				<template #icon>
					<NoteTextOutline />
				</template>
			</NcEmptyContent>
			<LogDetails v-if="logStore.logItem && navigationStore.selected === 'logs'" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent } from '@nextcloud/vue'
import LogsList from './LogsList.vue'
import LogDetails from './LogDetails.vue'
import NoteTextOutline from 'vue-material-design-icons/NoteTextOutline.vue'

export default {
	name: 'LogsIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		LogsList,
		LogDetails,
		NoteTextOutline,
	},
}
</script>