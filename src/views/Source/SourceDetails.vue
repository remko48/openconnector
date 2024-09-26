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
						<b>URL:</b>
						<p>{{ sourceStore.sourceItem.url }}</p>
					</div>
				</div>
				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
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
							<div v-if="sourceStore?.sourceItem?.logs?.length">
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
							<div v-if="!sourceStore?.sourceItem?.logs?.length">
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
</style>
