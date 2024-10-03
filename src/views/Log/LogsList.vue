<script setup>
import { logStore, navigationStore, searchStore } from '../../store/store.js'
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
					@trailing-button-click="logStore.refreshLogList()">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="logStore.refreshLogList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
				</NcActions>
			</div>
			<div v-if="logStore.logList && logStore.logList.length > 0">
				<NcListItem v-for="(log, i) in logStore.logList"
					:key="`${log}${i}`"
					:name="log.message"
					:active="logStore.logItem?.id === log?.id"
					:force-display-actions="true"
					@click="logStore.setLogItem(log)">
					<template #icon>
						<NoteTextOutline :class="logStore.logItem?.id === log.id && 'selectedLogIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ log?.timestamp }}
					</template>
					<template #actions>
						<NcActionButton @click="logStore.setLogItem(log); navigationStore.setDialog('deleteLog')">
							<template #icon>
								<TrashCanOutline />
							</template>
							Verwijderen
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="!logStore.logList"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Logs aan het laden" />

		<div v-if="!logStore.logList.length">
			Er zijn nog geen logs gedefinieerd.
		</div>
	</NcAppContentList>
</template>

<script>
import { NcListItem, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon, NcActions } from '@nextcloud/vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import NoteTextOutline from 'vue-material-design-icons/NoteTextOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'LogsList',
	components: {
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		Magnify,
		NoteTextOutline,
		Refresh,
		TrashCanOutline,
	},
	mounted() {
		logStore.refreshLogList()
	},
}
</script>

<style>
/* Styles remain the same */
</style>
