<script setup>
import { sourceStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<SourcesList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!sourceStore.sourceItem || navigationStore.selected != 'sources'"
				class="detailContainer"
				name="Geen bron"
				description="Nog geen bron geselecteerd">
				<template #icon>
					<Database />
				</template>
				<template #action>
					<NcButton type="primary" @click="sourceStore.setSourceItem({}); navigationStore.setModal('editSource')">
						Bron toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<SourceDetails v-if="sourceStore.sourceItem && navigationStore.selected === 'sources'" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import SourcesList from './SourcesList.vue'
import SourceDetails from './SourceDetails.vue'
import Database from 'vue-material-design-icons/Database.vue'

export default {
	name: 'SourcesIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		SourcesList,
		SourceDetails,
		Database,
	},
}
</script>
