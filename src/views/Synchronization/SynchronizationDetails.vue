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
						<b>Status:</b>
						<p>{{ synchronizationStore.synchronizationItem.status }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Last Run:</b>
						<p>{{ synchronizationStore.synchronizationItem.lastRun }}</p>
					</div>
				</div>
				<!-- Add more synchronization-specific details here -->

				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab title="Contracts">
							<div v-if="contracts.length">
								<NcListItem v-for="(value, key, i) in contracts"
									:key="`${key}${i}`"
									:name="key"
									:bold="false"
									:force-display-actions="true"
									:active="false">
									<template #icon>
										<FileCertificateOutline
											disable-menu
											:size="44" />
									</template>
									<template #subname>
										{{ value }}
									</template>
									<template #actions>
										<NcActionButton @click="editJobArgument(key)">
											<template #icon>
												<Pencil :size="20" />
											</template>
											Edit
										</NcActionButton>
										<NcActionButton @click="deleteJobArgument(key)">
											<template #icon>
												<Delete :size="20" />
											</template>
											Delete
										</NcActionButton>
									</template>
								</NcListItem>
							</div>
							<div v-if="!contracts.length" class="tabPanel">
								No contracts found
							</div>
						</BTab>
						<BTab title="Logs">
							<div v-if="synchronizationStore.synchronizationLogs?.length">
								<NcListItem v-for="(log, i) in synchronizationStore.synchronizationLogs"
									:key="log.id + i"
									:name="log.id"
									:bold="false"
									:force-display-actions="true">
									<template #icon>
										<BriefcaseAccountOutline disable-menu
											:size="44" />
									</template>
									<template #subname>
										{{ log.created }}
									</template>
								</NcListItem>
							</div>
							<div v-if="!synchronizationStore.synchronizationLogs?.length" class="tabPanel">
								No logs found
							</div>
						</BTab>
					</BTabs>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { NcActions, NcActionButton, NcListItem } from '@nextcloud/vue'
import { BTabs, BTab } from 'bootstrap-vue'
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import FileCertificateOutline from 'vue-material-design-icons/FileCertificateOutline.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'

export default {
	name: 'SynchronizationDetails',
	components: {
		NcActions,
		NcActionButton,
		DotsHorizontal,
		Pencil,
		TrashCanOutline,
	},
	data() {
		return {
			contracts: [],
		}
	},
}
</script>

<style>
/* Styles remain the same */
</style>
