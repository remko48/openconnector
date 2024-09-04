<script setup>
import { logStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<div>
				<div class="head">
					<h1 class="h1">
						{{ logStore.logItem.message }}
					</h1>

					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setDialog('deleteLog')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</NcActions>
				</div>
				<span>{{ logStore.logItem.timestamp }}</span>

				<div class="detailGrid">
					<div class="gridContent gridFullWidth">
						<b>Level:</b>
						<p>{{ logStore.logItem.level }}</p>
					</div>
				</div>
				<!-- Add more log-specific details here -->
			</div>
		</div>
	</div>
</template>

<script>
import { NcActions, NcActionButton } from '@nextcloud/vue'
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'LogDetails',
	components: {
		NcActions,
		NcActionButton,
		DotsHorizontal,
		TrashCanOutline,
	},
}
</script>

<style>
/* Styles remain the same */
</style>