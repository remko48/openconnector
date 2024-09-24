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
				name="Geen synchronisatie"
				description="Nog geen synchronisatie geselecteerd">
				<template #icon>
					<VectorPolylinePlus />
				</template>
				<template #action>
					<NcButton type="primary" @click="synchronizationStore.setSynchronizationItem({}); navigationStore.setModal('editSynchronization')">
						Synchronisatie toevoegen
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
import VectorPolylinePlus from 'vue-material-design-icons/VectorPolylinePlus.vue'

export default {
	name: 'SynchronizationsIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		SynchronizationsList,
		SynchronizationDetails,
		VectorPolylinePlus,
	},
}
</script>
