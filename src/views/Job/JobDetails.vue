<script setup>
import { jobStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<div>
				<div class="detailHeader">
					<h1 class="h1">
						{{ jobStore.jobItem.name }}
					</h1>

					<NcActions :primary="true" menu-name="Actions">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editJob')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('editJobArgument')">
							<template #icon>
								<Plus :size="20" />
							</template>
							Add Argument
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('testJob')">
							<template #icon>
								<Update :size="20" />
							</template>
							Test
						</NcActionButton>

						<NcActionButton @click="refreshJobLogs()">
							<template #icon>
								<Sync :size="20" />
							</template>
							Refresh Logs
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteJob')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Delete
						</NcActionButton>
					</NcActions>
				</div>
				<span>{{ jobStore.jobItem.description }}</span>

				<div class="detailGrid">
					<div class="gridContent gridFullWidth">
						<b>Status:</b>
						<p>{{ jobStore.jobItem.status }}</p>
					</div>
				</div>

				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab title="Job Arguments">
							<div v-if="jobStore.jobItem?.arguments !== null && Object.keys(jobStore.jobItem?.arguments).length > 0">
								<NcListItem v-for="(value, key, i) in jobStore.jobItem?.arguments"
									:key="`${key}${i}`"
									:name="key"
									:bold="false"
									:force-display-actions="true"
									:active="jobStore.jobArgumentKey === key"
									@click="setActiveJobArgumentKey(key)">
									<template #icon>
										<SitemapOutline
											:class="jobStore.jobArgumentKey === key && 'selectedZaakIcon'"
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
							<div v-if="jobStore.jobItem?.arguments === null || Object.keys(jobStore.jobItem?.arguments).length === 0" class="tabPanel">
								No arguments found
							</div>
						</BTab>
						<BTab title="Logs">
							<div v-if="jobStore.jobLogs?.length">
								<NcListItem v-for="(log, i) in jobStore.jobLogs"
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
							<div v-if="!jobStore.jobLogs?.length" class="tabPanel">
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
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import SitemapOutline from 'vue-material-design-icons/SitemapOutline.vue'
import Update from 'vue-material-design-icons/Update.vue'
import Sync from 'vue-material-design-icons/Sync.vue'

export default {
	name: 'JobDetails',
	components: {
		NcActions,
		NcActionButton,
		DotsHorizontal,
		Pencil,
		TrashCanOutline,
		Update,
		BTabs,
		BTab,
		NcListItem,
	},
	mounted() {
		jobStore.refreshJobLogs()
	},
	methods: {
		deleteJobArgument(key) {
			jobStore.setJobArgumentKey(key)
			navigationStore.setModal('deleteJobArgument')
		},
		editJobArgument(key) {
			jobStore.setJobArgumentKey(key)
			navigationStore.setModal('editJobArgument')
		},
		setActiveJobArgumentKey(jobArgumentKey) {
			if (jobStore.jobArgumentKey === jobArgumentKey) {
				jobStore.setJobArgumentKey(false)
			} else { jobStore.setJobArgumentKey(jobArgumentKey) }
		},
		refreshJobLogs() {
			jobStore.refreshJobLogs()
		},
	},
}
</script>

<style>
/* Styles remain the same */
</style>
