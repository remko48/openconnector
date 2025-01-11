<script setup>
import { ruleStore, navigationStore, logStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<div>
				<div class="detailHeader">
					<h1 class="h1">
						{{ ruleStore.ruleItem.name }}
					</h1>

					<NcActions :primary="true" menu-name="Actions">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editRule')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit
						</NcActionButton>
						<NcActionButton @click="addRuleArgument()">
							<template #icon>
								<Plus :size="20" />
							</template>
							Add Argument
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('testRule')">
							<template #icon>
								<Update :size="20" />
							</template>
							Test
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('runRule')">
							<template #icon>
								<Play :size="20" />
							</template>
							Run
						</NcActionButton>

						<NcActionButton @click="refreshRuleLogs()">
							<template #icon>
								<Sync :size="20" />
							</template>
							Refresh Logs
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteRule')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Delete
						</NcActionButton>
					</NcActions>
				</div>
				<span>{{ ruleStore.ruleItem.description }}</span>

				<div class="detailGrid">
					<div class="gridContent gridFullWidth">
						<b>id:</b>
						<p>{{ ruleStore.ruleItem.id }}</p>
					</div>
					<div class="gridContent">
						<b>Status:</b>
						<p>{{ ruleStore.ruleItem.status }}</p>
					</div>
					<div class="gridContent">
						<b>Enabled:</b>
						<p>{{ ruleStore.ruleItem.isEnabled }}</p>
					</div>
					<div class="gridContent">
						<b>Rule Class:</b>
						<p>{{ ruleStore.ruleItem.ruleClass }}</p>
					</div>
					<div class="gridContent">
						<b>Interval:</b>
						<p>{{ ruleStore.ruleItem.interval }}</p>
					</div>
					<div class="gridContent">
						<b>Execution Time:</b>
						<p>{{ ruleStore.ruleItem.executionTime }}</p>
					</div>
					<div class="gridContent">
						<b>Time Sensitive:</b>
						<p>{{ ruleStore.ruleItem.timeSensitive }}</p>
					</div>
					<div class="gridContent">
						<b>Allow Parallel Runs:</b>
						<p>{{ ruleStore.ruleItem.allowParallelRuns }}</p>
					</div>
					<div class="gridContent">
						<b>Single Run:</b>
						<p>{{ ruleStore.ruleItem.singleRun }}</p>
					</div>
					<div class="gridContent">
						<b>Next Run:</b>
						<p>
							{{ getValidISOstring(ruleStore.ruleItem.nextRun) ? new Date(ruleStore.ruleItem.nextRun).toLocaleString() : 'N/A' }}
						</p>
					</div>
					<div class="gridContent">
						<b>Last Run:</b>
						<p>
							{{ getValidISOstring(ruleStore.ruleItem.lastRun) ? new Date(ruleStore.ruleItem.lastRun).toLocaleString() : 'N/A' }}
						</p>
					</div>
				</div>

				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab title="Rule Arguments">
							<div v-if="ruleStore.ruleItem?.arguments !== null && Object.keys(ruleStore.ruleItem?.arguments).length > 0">
								<NcListItem v-for="(value, key, i) in ruleStore.ruleItem?.arguments"
									:key="`${key}${i}`"
									:name="key"
									:bold="false"
									:force-display-actions="true"
									:active="ruleStore.ruleArgumentKey === key"
									@click="setActiveRuleArgumentKey(key)">
									<template #icon>
										<SitemapOutline
											:class="ruleStore.ruleArgumentKey === key && 'selectedZaakIcon'"
											disable-menu
											:size="44" />
									</template>
									<template #subname>
										{{ value }}
									</template>
									<template #actions>
										<NcActionButton @click="editRuleArgument(key)">
											<template #icon>
												<Pencil :size="20" />
											</template>
											Edit
										</NcActionButton>
										<NcActionButton @click="deleteRuleArgument(key)">
											<template #icon>
												<Delete :size="20" />
											</template>
											Delete
										</NcActionButton>
									</template>
								</NcListItem>
							</div>
							<div v-if="ruleStore.ruleItem?.arguments === null || !Object.keys(ruleStore.ruleItem?.arguments).length" class="tabPanel">
								No arguments found
							</div>
						</BTab>
						<BTab title="Logs">
							<div v-if="ruleStore.ruleLogs?.length">
								<NcListItem v-for="(log, i) in ruleStore.ruleLogs"
									:key="log.id + i"
									:class="getLevelColor(log.level)"
									:name="log.message"
									:bold="false"
									:counter-number="log.level"
									:force-display-actions="true"
									:active="logStore.activeLogKey === `ruleLog-${log.id}`"
									@click="setActiveRuleLog(log.id)">
									>
									<template #icon>
										<TimelineQuestionOutline disable-menu
											:size="44" />
									</template>
									<template #subname>
										{{ new Date(log.created).toLocaleString() }}
									</template>
									<template #actions>
										<NcActionButton @click="viewLog(log)">
											<template #icon>
												<EyeOutline :size="20" />
											</template>
											View
										</NcActionButton>
									</template>
								</NcListItem>
							</div>
							<div v-if="!ruleStore.ruleLogs?.length" class="tabPanel">
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
import TimelineQuestionOutline from 'vue-material-design-icons/TimelineQuestionOutline.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import SitemapOutline from 'vue-material-design-icons/SitemapOutline.vue'
import Update from 'vue-material-design-icons/Update.vue'
import Sync from 'vue-material-design-icons/Sync.vue'
import EyeOutline from 'vue-material-design-icons/EyeOutline.vue'
import Play from 'vue-material-design-icons/Play.vue'

import getValidISOstring from '../../services/getValidISOstring.js'

export default {
	name: 'RuleDetails',
	components: {
		NcActions,
		NcActionButton,
		DotsHorizontal,
		Pencil,
		TrashCanOutline,
		Update,
		BTabs,
		BTab,
		NcListItem,
	},
	mounted() {
		ruleStore.refreshRuleLogs()
	},
	methods: {
		deleteRuleArgument(key) {
			ruleStore.setRuleArgumentKey(key)
			navigationStore.setModal('deleteRuleArgument')
		},
		editRuleArgument(key) {
			ruleStore.setRuleArgumentKey(key)
			navigationStore.setModal('editRuleArgument')
		},
		addRuleArgument() {
			ruleStore.setRuleArgumentKey(null)
			navigationStore.setModal('editRuleArgument')
		},
		setActiveRuleArgumentKey(ruleArgumentKey) {
			if (ruleStore.ruleArgumentKey === ruleArgumentKey) {
				ruleStore.setRuleArgumentKey(false)
			} else { ruleStore.setRuleArgumentKey(ruleArgumentKey) }
		},
		setActiveRuleLog(ruleLogId) {
			if (logStore.activeLogKey === `ruleLog-${ruleLogId}`) {
				logStore.setActiveLogKey(null)
			} else {
				logStore.setActiveLogKey(`ruleLog-${ruleLogId}`)
			}
		},
		viewLog(log) {
			logStore.setViewLogItem(log)
			navigationStore.setModal('viewRuleLog')
		},
		refreshRuleLogs() {
			ruleStore.refreshRuleLogs()
		},
		getLevelColor(level) {
			switch (level) {
			case 'SUCCESS':
				return 'successLevel'
			case 'INFO':
				return 'infoLevel'
			case 'NOTICE':
				return 'noticeLevel'
			case 'WARNING':
				return 'warningLevel'
			case 'ERROR':
				return 'errorLevel'
			case 'CRITICAL':
				return 'criticalLevel'
			case 'ALERT':
				return 'alertLevel'
			case 'EMERGENCY':
				return 'emergencyLevel'
			case 'DEBUG':
				return 'debugLevel'
			default:
				return 'debugLevel'
			}
		},

	},
}
</script>

<style>
	.successLevel * .counter-bubble__counter {
		background-color: var(--OC-color-status-background-success);
		color: var(--OC-color-status-success);
	}

	.errorLevel * .counter-bubble__counter {
		background-color: var(--OC-color-status-background-error);
		color: var(--OC-color-status-error);
	}

	.noticeLevel * .counter-bubble__counter {
		background-color: var(--OC-color-status-background-notice);
		color: var(--OC-color-status-notice);
	}

	.warningLevel * .counter-bubble__counter {
		background-color: var(--OC-color-status-background-warning);
		color: var(--OC-color-status-warning);
	}

	.infoLevel * .counter-bubble__counter {
		background-color: var(--OC-color-status-background-info);
		color: var(--OC-color-status-info);
	}

	.criticalLevel * .counter-bubble__counter {
		background-color: var(--OC-color-status-background-critical);
		color: var(--OC-color-status-critical);
	}

	.alertLevel * .counter-bubble__counter {
		background-color: var(--OC-color-status-background-alert);
		color: var(--OC-color-status-alert);
	}

	.emergencyLevel * .counter-bubble__counter {
		background-color: var(--OC-color-status-background-emergency);
		color: var(--OC-color-status-emergency);
	}

	.debugLevel * .counter-bubble__counter {
		background-color: var(--OC-color-status-background-debug);
		color: var(--OC-color-status-debug);
	}
</style>
