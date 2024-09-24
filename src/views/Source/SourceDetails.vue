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

					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editSource')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteSource')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
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
							<!-- Add mappings content here -->
						</BTab>
						<BTab title="Jobs">
							<!-- Add jobs content here -->
						</BTab>
						<BTab title="Logs">
							<!-- Add logs content here -->
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
import { NcActions, NcActionButton } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'SourceDetails',
	components: {
		NcActions,
		NcActionButton,
		BTabs,
		BTab,
		// Icons
		DotsHorizontal,
		Pencil,
		TrashCanOutline,
	},
}
</script>

<style>
/* Styles remain the same */
</style>
