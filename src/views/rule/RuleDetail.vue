<template>
	<NcAppContent>
		<NcEmptyContent v-if="loading" icon="icon-loading">
			{{ t('openconnector', 'Loading rule...') }}
		</NcEmptyContent>
		<template v-else>
			<div class="rule-detail">
				<div class="header">
					<h2>{{ rule.name }}</h2>
					<NcBadge :type="rule.active ? 'success' : 'error'">
						{{ rule.active ? t('openconnector', 'Active') : t('openconnector', 'Inactive') }}
					</NcBadge>
				</div>

				<div class="content">
					<div class="section">
						<h3>{{ t('openconnector', 'Details') }}</h3>
						<div class="details">
							<div class="detail-item">
								<label>{{ t('openconnector', 'Description') }}</label>
								<p>{{ rule.description || t('openconnector', 'No description') }}</p>
							</div>
							<div class="detail-item">
								<label>{{ t('openconnector', 'Condition') }}</label>
								<p>{{ rule.condition }}</p>
							</div>
							<div class="detail-item">
								<label>{{ t('openconnector', 'Created') }}</label>
								<p>{{ formatDate(rule.created) }}</p>
							</div>
							<div class="detail-item">
								<label>{{ t('openconnector', 'Last modified') }}</label>
								<p>{{ formatDate(rule.updated) }}</p>
							</div>
						</div>
					</div>

					<div class="section">
						<h3>{{ t('openconnector', 'Rule History') }}</h3>
						<div class="history">
							<!-- Add rule execution history here -->
						</div>
					</div>
				</div>

				<div class="actions">
					<NcButton @click="editRule">
						<template #icon>
							<Pencil :size="20" />
						</template>
						{{ t('openconnector', 'Edit') }}
					</NcButton>
					<NcButton type="error" @click="confirmDelete">
						<template #icon>
							<Delete :size="20" />
						</template>
						{{ t('openconnector', 'Delete') }}
					</NcButton>
				</div>
			</div>
		</template>
	</NcAppContent>
</template>

<script>
import {
	NcAppContent,
	NcEmptyContent,
	NcButton,
	NcBadge,
} from '@nextcloud/vue'
import { Pencil, Delete } from '@mdi/js'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { formatDate } from '@nextcloud/moment'

export default {
	name: 'RuleDetail',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		NcBadge,
	},
	data() {
		return {
			loading: true,
			rule: null,
		}
	},
	created() {
		this.loadRule()
	},
	methods: {
		formatDate,
		async loadRule() {
			try {
				const response = await axios.get(generateUrl(`/apps/openconnector/api/rules/${this.$route.params.id}`))
				this.rule = response.data
			} catch (error) {
				console.error('Error loading rule:', error)
				showError(t('openconnector', 'Could not load rule'))
			} finally {
				this.loading = false
			}
		},
		editRule() {
			// Implement edit functionality
		},
		async confirmDelete() {
			// Implement delete confirmation and functionality
		},
	},
}
</script>

<style scoped>
.rule-detail {
	padding: 20px;
	max-width: 800px;
	margin: 0 auto;
}

.header {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-bottom: 24px;
}

.section {
	margin-bottom: 32px;
}

.details {
	display: grid;
	gap: 16px;
}

.detail-item {
	display: grid;
	gap: 4px;
}

.detail-item label {
	font-weight: bold;
	color: var(--color-text-maxcontrast);
}

.actions {
	display: flex;
	gap: 12px;
	margin-top: 32px;
}
</style> 