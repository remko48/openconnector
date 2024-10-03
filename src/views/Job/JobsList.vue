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
						Refresh
					</NcActionButton>
					<NcActionButton @click="jobStore.setJobItem(null); navigationStore.setModal('editJob')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Add job
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
						<Update :class="jobStore.jobItem?.id === job.id && 'selectedJobIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ job?.description }}
					</template>
					<template #actions>
						<NcActionButton @click="jobStore.setJobItem(job); navigationStore.setModal('editJob')">
							<template #icon>
								<Pencil />
							</template>
							Edit
						</NcActionButton>
						<NcActionButton @click="jobStore.setJobItem(job); navigationStore.setDialog('deleteJob')">
							<template #icon>
								<TrashCanOutline />
							</template>
							Delete
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

		<div v-if="!jobStore.jobList.length" class="emptyListHeader">
			No jobs defined.
		</div>
	</NcAppContentList>
</template>

<script>
import { NcListItem, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon, NcActions } from '@nextcloud/vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Update from 'vue-material-design-icons/Update.vue'
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
		Update,
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
