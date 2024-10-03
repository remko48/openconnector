<script setup>
import { mappingStore, navigationStore, searchStore } from '../../store/store.js'
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
					@trailing-button-click="mappingStore.refreshMappingList()">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="mappingStore.refreshMappingList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
					<NcActionButton @click="mappingStore.setMappingItem({}); navigationStore.setModal('editMapping')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Mapping toevoegen
					</NcActionButton>
				</NcActions>
			</div>
			<div v-if="mappingStore.mappingList && mappingStore.mappingList.length > 0">
				<NcListItem v-for="(mapping, i) in mappingStore.mappingList"
					:key="`${mapping}${i}`"
					:name="mapping.name"
					:active="mappingStore.mappingItem?.id === mapping?.id"
					:force-display-actions="true"
					@click="mappingStore.setMappingItem(mapping)">
					<template #icon>
						<SitemapOutline :class="mappingStore.mappingItem?.id === mapping.id && 'selectedMappingIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ mapping?.description }}
					</template>
					<template #actions>
						<NcActionButton @click="mappingStore.setMappingItem(mapping); navigationStore.setModal('editMapping')">
							<template #icon>
								<Pencil />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="mappingStore.setMappingItem(mapping); navigationStore.setDialog('deleteMapping')">
							<template #icon>
								<TrashCanOutline />
							</template>
							Verwijderen
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="!mappingStore.mappingList"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Mappings aan het laden" />

		<div v-if="mappingStore.mappingList.length === 0" class="emptyListHeader">
			Er zijn nog geen mappings gedefinieerd.
		</div>
	</NcAppContentList>
</template>

<script>
import { NcListItem, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon, NcActions } from '@nextcloud/vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import SitemapOutline from 'vue-material-design-icons/SitemapOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'MappingsList',
	components: {
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		Magnify,
		SitemapOutline,
		Refresh,
		Plus,
		Pencil,
		TrashCanOutline,
	},
	mounted() {
		mappingStore.refreshMappingList()
	},
}
</script>

<style>
/* Styles remain the same */
</style>
