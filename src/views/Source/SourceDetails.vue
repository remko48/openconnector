<script setup>
import { sourceStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<div>
				<div class="detailHeader">
					<h1 class="h1">
						{{ sourceStore.sourceItem.name }}
					</h1>

					<NcActions :primary="true" menu-name="Actions">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editSource')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('testSource')">
							<template #icon>
								<Sync :size="20" />
							</template>
							Test
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('editSourceConfiguration')">
							<template #icon>
								<Plus :size="20" />
							</template>
							Add Configuration
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteSource')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Delete
						</NcActionButton>
					</NcActions>
				</div>
				<span>{{ sourceStore.sourceItem.description }}</span>

				<div class="detailGrid">
					<div class="gridContent gridFullWidth">
						<b>location:</b>
						<p>{{ sourceStore.sourceItem.location }}</p>
					</div>
				</div>
				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab title="Configurations">
							<div v-if="sourceStore.sourceItem?.configuration !== null && Object.keys(sourceStore.sourceItem?.configuration).length > 0">
								<NcListItem v-for="(value, key, i) in sourceStore.sourceItem?.configuration"
									:key="`${key}${i}`"
									:name="key"
									:bold="false"
									:force-display-actions="true"
									:active="sourceStore.sourceConfigurationKey === key"
									@click="setActiveSourceConfigurationKey(key)">
									<template #icon>
										<SitemapOutline
											:class="sourceStore.sourceConfigurationKey === key && 'selectedZaakIcon'"
											disable-menu
											:size="44" />
									</template>
									<template #subname>
										{{ value }}
									</template>
									<template #actions>
										<NcActionButton @click="editSourceConfiguration(key)">
											<template #icon>
												<Pencil :size="20" />
											</template>
											Edit
										</NcActionButton>
										<NcActionButton @click="deleteSourceConfiguration(key)">
											<template #icon>
												<Delete :size="20" />
											</template>
											Delete
										</NcActionButton>
									</template>
								</NcListItem>
							</div>
							<div v-if="sourceStore.sourceItem?.configuration === null || Object.keys(sourceStore.sourceItem?.configuration).length === 0" class="tabPanel">
								No configurations found
							</div>
						</BTab>
						<BTab title="Mappings">
							<div v-if="sourceStore?.sourceItem?.mappings?.length">
								<NcListItem v-for="(character, i) in filterCharacters"
									:key="character.id + i"
									:name="character.name"
									:bold="false"
									:force-display-actions="true">
									<template #icon>
										<BriefcaseAccountOutline disable-menu
											:size="44" />
									</template>
									<template #subname>
										{{ character.description }}
									</template>
								</NcListItem>
							</div>
							<div v-if="!sourceStore?.sourceItem?.mappings?.length">
								No mappings found
							</div>
						</BTab>
						<BTab title="Jobs">
							<div v-if="sourceStore?.sourceItem?.jobs?.length">
								<NcListItem v-for="(character, i) in filterCharacters"
									:key="character.id + i"
									:name="character.name"
									:bold="false"
									:force-display-actions="true">
									<template #icon>
										<BriefcaseAccountOutline disable-menu
											:size="44" />
									</template>
									<template #subname>
										{{ character.description }}
									</template>
								</NcListItem>
							</div>
							<div v-if="!sourceStore?.sourceItem?.jobs?.length">
								No jobs found
							</div>
						</BTab>
						<BTab title="Logs">
							<div v-if="sourceStore.sourceLogs?.length">
								<NcListItem v-for="(log, i) in sourceStore.sourceLogs"
									:key="log.id + i"
									:class="log.status === 'error' ? 'errorStatus' : 'okStatus'"
									:name="log.response.body"
									:bold="false"
									:counter-number="log.statusCode"
									:force-display-actions="true">
									<template #counter-number>
										<BriefcaseAccountOutline disable-menu
											:size="44" />
									</template>
									<template #icon>
										<BriefcaseAccountOutline disable-menu
											:size="44" />
									</template>
									<template #subname>
										{{ new Date(log.createdAt.date) }}
									</template>
								</NcListItem>
							</div>
							<div v-if="!sourceStore.sourceLogs?.length">
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
// Components
import { BTabs, BTab } from 'bootstrap-vue'
import { NcActions, NcActionButton, NcListItem } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Sync from 'vue-material-design-icons/Sync.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import SitemapOutline from 'vue-material-design-icons/SitemapOutline.vue'
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
	name: 'SourceDetails',
	components: {
		NcActions,
		NcActionButton,
		BTabs,
		BTab,
		NcListItem,
		// Icons
		DotsHorizontal,
		Pencil,
		TrashCanOutline,
		Sync,
	},
	mounted() {
		this.refreshSourceLogs()
	},
	methods: {
		deleteSourceConfiguration(key) {
			sourceStore.setSourceConfigurationKey(key)
			navigationStore.setModal('deleteSourceConfiguration')
		},
		editSourceConfiguration(key) {
			sourceStore.setSourceConfigurationKey(key)
			navigationStore.setModal('editSourceConfiguration')
		},
		setActiveSourceConfigurationKey(sourceConfigurationKey) {
			if (sourceStore.sourceConfigurationKey === sourceConfigurationKey) {
				sourceStore.setSourceConfigurationKey(false)
			} else { sourceStore.setSourceConfigurationKey(sourceConfigurationKey) }
		},
		refreshSourceLogs() {
			sourceStore.refreshSourceLogs()

		},
	},
}
</script>

<style>
.h1 {
	display: block !important;
	font-size: 2em !important;
	margin-block-start: 0.67em !important;
	margin-block-end: 0.67em !important;
	margin-inline-start: 0px !important;
	margin-inline-end: 0px !important;
	font-weight: bold !important;
	unicode-bidi: isolate !important;
  }

  .okStatus * .counter-bubble__counter {
	background-color: green;
	color: white
  }

  .errorStatus * .counter-bubble__counter {
	background-color: red;
	color: white
  }
</style>
