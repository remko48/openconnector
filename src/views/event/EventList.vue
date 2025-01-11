<template>
	<div class="event-list">
		<table>
			<thead>
				<tr>
					<th>{{ t('openconnector', 'Name') }}</th>
					<th>{{ t('openconnector', 'Description') }}</th>
					<th>{{ t('openconnector', 'Created') }}</th>
					<th>{{ t('openconnector', 'Status') }}</th>
					<th>{{ t('openconnector', 'Actions') }}</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="event in events"
					:key="event.id">
					<td>
						<router-link :to="{ name: 'event-detail', params: { id: event.id }}">
							{{ event.name }}
						</router-link>
					</td>
					<td>{{ event.description }}</td>
					<td>{{ formatDate(event.created) }}</td>
					<td>
						<NcBadge :type="event.active ? 'success' : 'error'">
							{{ event.active ? t('openconnector', 'Active') : t('openconnector', 'Inactive') }}
						</NcBadge>
					</td>
					<td class="actions">
						<NcActions>
							<NcActionButton @click="$router.push({ name: 'event-detail', params: { id: event.id }})">
								<template #icon>
									<Eye :size="20" />
								</template>
								{{ t('openconnector', 'View') }}
							</NcActionButton>
							<NcActionButton @click="$emit('edit', event)">
								<template #icon>
									<Pencil :size="20" />
								</template>
								{{ t('openconnector', 'Edit') }}
							</NcActionButton>
							<NcActionButton @click="$emit('delete', event)">
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
	</div>
</template>

<script>
import {
	NcActions,
	NcActionButton,
	NcBadge,
} from '@nextcloud/vue'
import { Eye, Pencil, Delete } from '@mdi/js'
import { formatDate } from '@nextcloud/moment'

export default {
	name: 'EventList',
	components: {
		NcActions,
		NcActionButton,
		NcBadge,
	},
	props: {
		events: {
			type: Array,
			required: true,
		},
	},
	methods: {
		formatDate,
	},
}
</script>

<style scoped>
.event-list {
	width: 100%;
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

a {
	color: var(--color-primary);
	text-decoration: none;
}

a:hover {
	text-decoration: underline;
}
</style> 