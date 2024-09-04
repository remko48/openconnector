<script setup>
import { jobStore, navigationStore, searchStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
			<ul>
				<div class="listHeader">
					<NcTextField
						:value.sync="searchStore.search"
						:show-trailing-button="searchStore.search !== ''"
						label="Search"
						class="searchField"
						trailing-button-icon="close"
						@trailing-button-click="jobStore.refreshJobList()">
						<Magnify :size="20" />
					</NcTextField>
					<NcActions>
						<NcActionButton @click="jobStore.refreshJobList()">
							<template #icon>
								<Refresh :size="20" />
							</template>
							Ververs
						</NcActionButton>
						<NcActionButton @click="jobStore.setJobItem({}); navigationStore.setModal('editJob')">
							<template #icon>
								<Plus :size="20" />
							</template>
							Taak toevoegen
						</NcActionButton>
					</NcActions>
				</div>
				<div v-if="jobStore.jobList && jobStore.jobList.length > 0">
					<NcListItem v-for="(job, i) in jobStore.jobList"
						:key="`${job}${i}`"
						:name="job.name"
						:active="jobStore.jobItem?.id === job?.id"
						:force-display-actions="true"
						@click="jobStore.setJobItem(job)">
						<template #icon>
							<Briefcase :class="jobStore.jobItem?.id === job.id && 'selectedJobIcon'"
								disable-menu
								:size="44" />
						</template>
						<template #subname>
							{{ job?.description }}
						</template>
						<template #actions>
							<NcActionButton @click="jobStore.setJobItem(job); navigationStore.setModal('editJob')">
								<template #icon>
									<Pencil/>
								</template>
								Bewerken
							</NcActionButton>
							<NcActionButton @click="jobStore.setJobItem(job); navigationStore.setDialog('deleteJob')">
								<template #icon>
									<TrashCanOutline/>
								</template>
								Verwijderen
							</NcActionButton>
						</template>
					</NcListItem>
				</div>
			</ul> 

			<NcLoadingIcon v-if="!jobStore.jobList"
				class="loadingIcon"
				:size="64"
				appearance="dark"
				name="Taken aan het laden" />

			<div v-if="jobStore.jobList.length === 0">
				Er zijn nog geen taken gedefinieerd.
			</div>
	</NcAppContentList>
</template>

<script>
import { NcListItem, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon, NcActions } from '@nextcloud/vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Briefcase from 'vue-material-design-icons/Briefcase.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'JobsList',
	components: {
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		Magnify,
		Briefcase,
		Refresh,
		Plus,
		Pencil,
		TrashCanOutline,
	},
	mounted() {
		jobStore.refreshJobList()
	},
}
</script>

<style>
/* Styles remain the same */
</style>