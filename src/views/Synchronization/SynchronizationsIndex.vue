<script setup>
import { synchronizationStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<SynchronizationsList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!synchronizationStore.synchronizationItem || navigationStore.selected != 'synchronizations'"
				class="detailContainer"
				name="No synchronization"
				description="No synchronization selected">
				<template #icon>
					<SyncCircle />
				</template>
				<template #action>
					<NcButton type="primary" @click="synchronizationStore.setSynchronizationItem(null); navigationStore.setModal('editSynchronization')">
						Add synchronization
					</NcButton>
				</template>
			</NcEmptyContent>
			<SynchronizationDetails v-if="synchronizationStore.synchronizationItem && navigationStore.selected === 'synchronizations'" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import SynchronizationsList from './SynchronizationsList.vue'
import SynchronizationDetails from './SynchronizationDetails.vue'
import SyncCircle from 'vue-material-design-icons/SyncCircle.vue'

export default {
	name: 'SynchronizationsIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		SynchronizationsList,
		SynchronizationDetails,
		SyncCircle,
	},
}
</script>
