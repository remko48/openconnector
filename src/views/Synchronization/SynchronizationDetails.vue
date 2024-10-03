<script setup>
import { synchronizationStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<div>
				<div class="detailHeader">
					<h1 class="h1">
						{{ synchronizationStore.synchronizationItem.name }}
					</h1>

					<NcActions :primary="true" menu-name="Actions">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editSynchronization')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteSynchronization')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Delete
						</NcActionButton>
					</NcActions>
				</div>
				<span>{{ synchronizationStore.synchronizationItem.description }}</span>

				<div class="detailGrid">
					<div class="gridContent gridFullWidth">
						<b>Created:</b>
						<p>{{ synchronizationStore.synchronizationItem.created || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Updated:</b>
						<p>{{ synchronizationStore.synchronizationItem.updated || 'N/A' }}</p>
					</div>

					<div class="gridContent gridDoubleWidth">
						<h4>Source</h4>
					</div>
					<div class="gridContent gridFullWidth">
						<b>SourceId:</b>
						<p>{{ synchronizationStore.synchronizationItem.sourceId || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Source Type:</b>
						<p>{{ synchronizationStore.synchronizationItem.sourceType || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Source Config:</b>
						<p>{{ synchronizationStore.synchronizationItem.sourceConfig || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Source Hash:</b>
						<p>{{ synchronizationStore.synchronizationItem.sourceHash || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Source Last Changed:</b>
						<p>{{ synchronizationStore.synchronizationItem.sourceLastChanged || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Source Last Checked:</b>
						<p>{{ synchronizationStore.synchronizationItem.sourceLastChecked || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Source Last Synced:</b>
						<p>{{ synchronizationStore.synchronizationItem.sourceLastSynced || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Source Target Mapping:</b>
						<p>{{ synchronizationStore.synchronizationItem.sourceTargetMapping || 'N/A' }}</p>
					</div>

					<div class="gridContent gridDoubleWidth">
						<h4>Target</h4>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Target Id:</b>
						<p>{{ synchronizationStore.synchronizationItem.targetId || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Target Type:</b>
						<p>{{ synchronizationStore.synchronizationItem.targetType || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Target Last Changed:</b>
						<p>{{ synchronizationStore.synchronizationItem.targetLastChanged || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Target Last Checked:</b>
						<p>{{ synchronizationStore.synchronizationItem.targetLastChecked || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Target Last Synced:</b>
						<p>{{ synchronizationStore.synchronizationItem.targetLastSynced || 'N/A' }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Target Source Mapping:</b>
						<p>{{ synchronizationStore.synchronizationItem.targetSourceMapping || 'N/A' }}</p>
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
.gridDoubleWidth {
	grid-column: span 2;
}
</style>
