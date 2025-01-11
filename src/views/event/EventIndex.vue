<template>
	<NcAppContent>
		<NcEmptyContent v-if="loading" icon="icon-loading">
			{{ t('openconnector', 'Loading events...') }}
		</NcEmptyContent>
		<template v-else>
			<NcAppNavigationItem
				:title="t('openconnector', 'Events')"
				icon="MessageTextFastOutline">
				<template #actions>
					<NcButton @click="showNewEventModal = true">
						<template #icon>
							<Plus :size="20" />
						</template>
						{{ t('openconnector', 'New event') }}
					</NcButton>
				</template>
			</NcAppNavigationItem>

			<div class="events">
				<table v-if="events.length > 0">
					<thead>
						<tr>
							<th>{{ t('openconnector', 'Name') }}</th>
							<th>{{ t('openconnector', 'Description') }}</th>
							<th>{{ t('openconnector', 'Status') }}</th>
							<th>{{ t('openconnector', 'Actions') }}</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="event in events"
							:key="event.id">
							<td>{{ event.name }}</td>
							<td>{{ event.description }}</td>
							<td>
								<NcBadge :type="event.active ? 'success' : 'error'">
									{{ event.active ? t('openconnector', 'Active') : t('openconnector', 'Inactive') }}
								</NcBadge>
							</td>
							<td class="actions">
								<NcActions>
									<NcActionButton @click="editEvent(event)">
										<template #icon>
											<Pencil :size="20" />
										</template>
										{{ t('openconnector', 'Edit') }}
									</NcActionButton>
									<NcActionButton @click="deleteEvent(event)">
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
					icon="MessageTextFastOutline"
					:title="t('openconnector', 'No events found')">
					{{ t('openconnector', 'Create your first event to get started') }}
					<template #action>
						<NcButton @click="showNewEventModal = true">
							<template #icon>
								<Plus :size="20" />
							</template>
							{{ t('openconnector', 'Create event') }}
						</NcButton>
					</template>
				</NcEmptyContent>
			</div>

			<NcModal v-if="showNewEventModal"
				@close="showNewEventModal = false">
				<div class="modal-content">
					<h2>{{ t('openconnector', 'New Event') }}</h2>
					<form @submit.prevent="createEvent">
						<NcTextField
							v-model="newEvent.name"
							:label="t('openconnector', 'Name')"
							required />
						<NcTextField
							v-model="newEvent.description"
							:label="t('openconnector', 'Description')"
							type="textarea" />
						<div class="button-group">
							<NcButton type="submit"
								:disabled="!newEvent.name">
								{{ t('openconnector', 'Create') }}
							</NcButton>
							<NcButton type="button"
								@click="showNewEventModal = false">
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
import { Plus, Pencil, Delete, MessageTextFastOutline } from '@mdi/js'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'

export default {
	name: 'EventIndex',
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
			events: [],
			showNewEventModal: false,
			newEvent: {
				name: '',
				description: '',
			},
		}
	},
	created() {
		this.loadEvents()
	},
	methods: {
		async loadEvents() {
			try {
				const response = await axios.get(generateUrl('/apps/openconnector/api/events'))
				this.events = response.data
			} catch (error) {
				console.error('Error loading events:', error)
				showError(t('openconnector', 'Could not load events'))
			} finally {
				this.loading = false
			}
		},
		async createEvent() {
			try {
				await axios.post(generateUrl('/apps/openconnector/api/events'), this.newEvent)
				showSuccess(t('openconnector', 'Event created successfully'))
				this.showNewEventModal = false
				this.newEvent = { name: '', description: '' }
				await this.loadEvents()
			} catch (error) {
				console.error('Error creating event:', error)
				showError(t('openconnector', 'Could not create event'))
			}
		},
		async editEvent(event) {
			// Implement edit functionality
		},
		async deleteEvent(event) {
			// Implement delete functionality
		},
	},
}
</script>

<style scoped>
.events {
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