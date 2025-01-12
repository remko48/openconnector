<script setup>
import { endpointStore, navigationStore, ruleStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<div>
				<div class="detailHeader">
					<h1 class="h1">
						{{ endpointStore.endpointItem.name }}
					</h1>

					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editEndpoint')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="endpointStore.exportEndpoint(endpointStore.endpointItem)">
							<template #icon>
								<FileExportOutline :size="20" />
							</template>
							Export endpoint
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteEndpoint')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</NcActions>
				</div>

				<div class="detailGrid">
					<div class="gridContent gridFullWidth">
						<b>uuid:</b>
						<p>{{ endpointStore.endpointItem.uuid }}</p>
					</div>
					<div class="gridContent gridFullWidth" />

					<div class="gridContent gridFullWidth">
						<b>Name:</b>
						<p>{{ endpointStore.endpointItem.name }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Description:</b>
						<p>{{ endpointStore.endpointItem.description }}</p>
					</div>

					<div class="gridContent gridFullWidth">
						<b>Version:</b>
						<p>{{ endpointStore.endpointItem.version }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Endpoint:</b>
						<p>{{ endpointStore.endpointItem.endpoint }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Endpoint Array:</b>
						<p>{{ endpointStore.endpointItem.endpointArray.join(', ') }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Endpoint Regex:</b>
						<p>{{ endpointStore.endpointItem.endpointRegex }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Method:</b>
						<p>{{ endpointStore.endpointItem.method }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Target Type:</b>
						<p>{{ endpointStore.endpointItem.targetType }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>Target Id:</b>
						<p>{{ endpointStore.endpointItem.targetId }}</p>
					</div>

					<div class="gridContent gridFullWidth">
						<b>created:</b>
						<p>{{ endpointStore.endpointItem.created }}</p>
					</div>
					<div class="gridContent gridFullWidth">
						<b>updated:</b>
						<p>{{ endpointStore.endpointItem.updated }}</p>
					</div>
				</div>

				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<!-- Rules Tab -->
						<BTab title="Rules">
							<div v-if="endpointStore.endpointItem?.rules?.length">
								<NcListItem v-for="ruleId in endpointStore.endpointItem.rules"
									:key="ruleId"
									:name="getRuleName(ruleId)"
									:bold="false"
									:force-display-actions="true"
									@click="viewRule(ruleId)">
									<template #icon>
										<SitemapOutline
											disable-menu
											:size="44" />
									</template>
									<template #subname>
										{{ getRuleType(ruleId) }}
									</template>
									<template #actions>
										<NcActionButton @click.stop="editRule(ruleId)">
											<template #icon>
												<Pencil :size="20" />
											</template>
											Edit
										</NcActionButton>
										<NcActionButton @click.stop="deleteRule(ruleId)">
											<template #icon>
												<Delete :size="20" />
											</template>
											Delete
										</NcActionButton>
									</template>
								</NcListItem>
							</div>
							<div v-else class="tabPanel">
								No rules found
							</div>
						</BTab>

						<!-- Logs Tab -->
						<BTab title="Logs">
							<div class="tabPanel">
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
import { NcActions, NcActionButton, NcListItem } from '@nextcloud/vue'
import { BTabs, BTab } from 'bootstrap-vue'
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import FileExportOutline from 'vue-material-design-icons/FileExportOutline.vue'
import SitemapOutline from 'vue-material-design-icons/SitemapOutline.vue'
import Delete from 'vue-material-design-icons/Delete.vue'

export default {
	name: 'EndpointDetails',
	components: {
		NcActions,
		NcActionButton,
		NcListItem,
		BTabs,
		BTab,
		DotsHorizontal,
		Pencil,
		TrashCanOutline,
		FileExportOutline,
		SitemapOutline,
		Delete,
	},
	data() {
		return {
			rulesList: []
		}
	},
	mounted() {
		this.loadRules()
	},
	methods: {
		async loadRules() {
			if (endpointStore.endpointItem?.rules?.length) {
				await ruleStore.refreshRuleList()
				this.rulesList = ruleStore.ruleList
			}
		},
		getRuleName(ruleId) {
			const rule = this.rulesList.find(rule => rule.id === ruleId)
			return rule ? rule.name : `Rule ${ruleId}`
		},
		getRuleType(ruleId) {
			const rule = this.rulesList.find(rule => rule.id === ruleId)
			return rule ? rule.type : 'Unknown type'
		},
		viewRule(ruleId) {
			ruleStore.setRuleItem(this.rulesList.find(rule => rule.id === ruleId))
			navigationStore.setView('ruleDetails')
		},
		editRule(ruleId) {
			ruleStore.setRuleItem(this.rulesList.find(rule => rule.id === ruleId))
			navigationStore.setModal('editRule')
		},
		deleteRule(ruleId) {
			ruleStore.setRuleItem(this.rulesList.find(rule => rule.id === ruleId))
			navigationStore.setDialog('deleteRule')
		}
	}
}
</script>

<style scoped>
.detailContainer {
	padding: 20px;
}

.detailHeader {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20px;
}

.detailGrid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
	gap: 20px;
	margin: 20px 0;
}

.gridFullWidth {
	grid-column: 1 / -1;
}

.tabContainer {
	margin-top: 20px;
}

.tabPanel {
	padding: 20px;
	text-align: center;
	color: var(--color-text-maxcontrast);
}
</style>
