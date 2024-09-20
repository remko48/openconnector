<script setup>
import { synchronizationStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<div>
				<div class="head">
					<h1 class="h1">
						{{ synchronizationStore.synchronizationItem.name }}
					</h1>

					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editSynchronization')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteSynchronization')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</NcActions>
				</div>
				<span>{{ synchronizationStore.synchronizationItem.description }}</span>

				<div class="detailGrid">
					<div class="gridContent gridFullWidth">
						<b>Status:</b>
						<p>{{ synchronizationStore.synchronizationItem.status }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Last Run:</b>
						<p>{{ synchronizationStore.synchronizationItem.lastRun }}</p>
					</div>
				</div>
				<!-- Add more synchronization-specific details here -->
			</div>
		</div>
	</div>
</template>

<script>
import { NcActions, NcActionButton } from '@nextcloud/vue'
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'SynchronizationDetails',
	components: {
		NcActions,
		NcActionButton,
		DotsHorizontal,
		Pencil,
		TrashCanOutline,
	},
}
</script>

<style>
/* Styles remain the same */
</style>
