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
				name="No mapping"
				description="No mapping selected">
				<template #icon>
					<SitemapOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="mappingStore.setMappingItem(null); navigationStore.setModal('editMapping')">
						Add mapping
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
import SitemapOutline from 'vue-material-design-icons/SitemapOutline.vue'

export default {
	name: 'MappingsIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		MappingsList,
		MappingDetails,
		SitemapOutline,
	},
}
</script>
