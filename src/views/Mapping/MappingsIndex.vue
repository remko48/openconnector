<script setup>
import { mappingStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<MappingsList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!mappingStore.mappingItem || navigationStore.selected != 'mappings'"
				class="detailContainer"
				name="Geen mapping"
				description="Nog geen mapping geselecteerd">
				<template #icon>
					<MapMarker />
				</template>
				<template #action>
					<NcButton type="primary" @click="mappingStore.setMappingItem({}); navigationStore.setModal('editMapping')">
						Mapping toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<MappingDetails v-if="mappingStore.mappingItem && navigationStore.selected === 'mappings'" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import MappingsList from './MappingsList.vue'
import MappingDetails from './MappingDetails.vue'
import MapMarker from 'vue-material-design-icons/MapMarker.vue'

export default {
	name: 'MappingsIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		MappingsList,
		MappingDetails,
		MapMarker,
	},
}
</script>