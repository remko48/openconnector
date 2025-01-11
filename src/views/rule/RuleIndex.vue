<template>
	<NcAppContent>
		<NcEmptyContent v-if="loading" icon="icon-loading">
			{{ t('openconnector', 'Loading rules...') }}
		</NcEmptyContent>
		<template v-else>
			<NcAppNavigationItem
				:title="t('openconnector', 'Rules')"
				icon="SitemapOutline">
				<template #actions>
					<NcButton @click="showNewRuleModal = true">
						<template #icon>
							<Plus :size="20" />
						</template>
						{{ t('openconnector', 'New rule') }}
					</NcButton>
				</template>
			</NcAppNavigationItem>

			<div class="rules">
				<table v-if="rules.length > 0">
					<thead>
						<tr>
							<th>{{ t('openconnector', 'Name') }}</th>
							<th>{{ t('openconnector', 'Description') }}</th>
							<th>{{ t('openconnector', 'Condition') }}</th>
							<th>{{ t('openconnector', 'Status') }}</th>
							<th>{{ t('openconnector', 'Actions') }}</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="rule in rules"
							:key="rule.id">
							<td>{{ rule.name }}</td>
							<td>{{ rule.description }}</td>
							<td>{{ rule.condition }}</td>
							<td>
								<NcBadge :type="rule.active ? 'success' : 'error'">
									{{ rule.active ? t('openconnector', 'Active') : t('openconnector', 'Inactive') }}
								</NcBadge>
							</td>
							<td class="actions">
								<NcActions>
									<NcActionButton @click="editRule(rule)">
										<template #icon>
											<Pencil :size="20" />
										</template>
										{{ t('openconnector', 'Edit') }}
									</NcActionButton>
									<NcActionButton @click="deleteRule(rule)">
										<template #icon>
											<Delete :size="20" />
										</template>
										{{ t('openconnector', 'Delete') }}
									</NcActionButton>
								</NcActions>
							</td>
						</tr>
					</tbody>
				</table>
				<NcEmptyContent v-else
					icon="SitemapOutline"
					:title="t('openconnector', 'No rules found')">
					{{ t('openconnector', 'Create your first rule to get started') }}
					<template #action>
						<NcButton @click="showNewRuleModal = true">
							<template #icon>
								<Plus :size="20" />
							</template>
							{{ t('openconnector', 'Create rule') }}
						</NcButton>
					</template>
				</NcEmptyContent>
			</div>

			<NcModal v-if="showNewRuleModal"
				@close="showNewRuleModal = false">
				<div class="modal-content">
					<h2>{{ t('openconnector', 'New Rule') }}</h2>
					<form @submit.prevent="createRule">
						<NcTextField
							v-model="newRule.name"
							:label="t('openconnector', 'Name')"
							required />
						<NcTextField
							v-model="newRule.description"
							:label="t('openconnector', 'Description')"
							type="textarea" />
						<NcTextField
							v-model="newRule.condition"
							:label="t('openconnector', 'Condition')"
							required />
						<div class="button-group">
							<NcButton type="submit"
								:disabled="!newRule.name || !newRule.condition">
								{{ t('openconnector', 'Create') }}
							</NcButton>
							<NcButton type="button"
								@click="showNewRuleModal = false">
								{{ t('openconnector', 'Cancel') }}
							</NcButton>
						</div>
					</form>
				</div>
			</NcModal>
		</template>
	</NcAppContent>
</template>

<script>
import {
	NcAppContent,
	NcEmptyContent,
	NcButton,
	NcModal,
	NcTextField,
	NcActions,
	NcActionButton,
	NcAppNavigationItem,
	NcBadge,
} from '@nextcloud/vue'
import { Plus, Pencil, Delete, SitemapOutline } from '@mdi/js'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'

export default {
	name: 'RuleIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		NcModal,
		NcTextField,
		NcActions,
		NcActionButton,
		NcAppNavigationItem,
		NcBadge,
	},
	data() {
		return {
			loading: true,
			rules: [],
			showNewRuleModal: false,
			newRule: {
				name: '',
				description: '',
				condition: '',
			},
		}
	},
	created() {
		this.loadRules()
	},
	methods: {
		async loadRules() {
			try {
				const response = await axios.get(generateUrl('/apps/openconnector/api/rules'))
				this.rules = response.data
			} catch (error) {
				console.error('Error loading rules:', error)
				showError(t('openconnector', 'Could not load rules'))
			} finally {
				this.loading = false
			}
		},
		async createRule() {
			try {
				await axios.post(generateUrl('/apps/openconnector/api/rules'), this.newRule)
				showSuccess(t('openconnector', 'Rule created successfully'))
				this.showNewRuleModal = false
				this.newRule = { name: '', description: '', condition: '' }
				await this.loadRules()
			} catch (error) {
				console.error('Error creating rule:', error)
				showError(t('openconnector', 'Could not create rule'))
			}
		},
		async editRule(rule) {
			// Implement edit functionality
		},
		async deleteRule(rule) {
			// Implement delete functionality
		},
	},
}
</script>

<style scoped>
.rules {
	padding: 20px;
}

table {
	width: 100%;
	border-collapse: collapse;
}

th, td {
	padding: 12px;
	text-align: left;
	border-bottom: 1px solid var(--color-border);
}

.actions {
	width: 100px;
}

.modal-content {
	padding: 20px;
}

.button-group {
	display: flex;
	gap: 10px;
	margin-top: 20px;
}
</style> 