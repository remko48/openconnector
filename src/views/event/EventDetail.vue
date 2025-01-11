<template>
	<NcAppContent>
		<NcEmptyContent v-if="loading" icon="icon-loading">
			{{ t('openconnector', 'Loading event...') }}
		</NcEmptyContent>
		<template v-else>
			<div class="event-detail">
				<div class="header">
					<h2>{{ event.name }}</h2>
					<NcBadge :type="event.active ? 'success' : 'error'">
						{{ event.active ? t('openconnector', 'Active') : t('openconnector', 'Inactive') }}
					</NcBadge>
				</div>

				<div class="content">
					<div class="section">
						<h3>{{ t('openconnector', 'Details') }}</h3>
						<div class="details">
							<div class="detail-item">
								<label>{{ t('openconnector', 'Description') }}</label>
								<p>{{ event.description || t('openconnector', 'No description') }}</p>
							</div>
							<div class="detail-item">
								<label>{{ t('openconnector', 'Created') }}</label>
								<p>{{ formatDate(event.created) }}</p>
							</div>
							<div class="detail-item">
								<label>{{ t('openconnector', 'Last modified') }}</label>
								<p>{{ formatDate(event.updated) }}</p>
							</div>
						</div>
					</div>

					<div class="section">
						<h3>{{ t('openconnector', 'Event History') }}</h3>
						<div class="history">
							<!-- Add event history/logs here -->
						</div>
					</div>
				</div>

				<div class="actions">
					<NcButton @click="editEvent">
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
	name: 'EventDetail',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		NcBadge,
	},
	data() {
		return {
			loading: true,
			event: null,
		}
	},
	created() {
		this.loadEvent()
	},
	methods: {
		formatDate,
		async loadEvent() {
			try {
				const response = await axios.get(generateUrl(`/apps/openconnector/api/events/${this.$route.params.id}`))
				this.event = response.data
			} catch (error) {
				console.error('Error loading event:', error)
				showError(t('openconnector', 'Could not load event'))
			} finally {
				this.loading = false
			}
		},
		editEvent() {
			// Implement edit functionality
		},
		async confirmDelete() {
			// Implement delete confirmation and functionality
		},
	},
}
</script>

<style scoped>
.event-detail {
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