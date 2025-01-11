<template>
	<div class="rule-list">
		<table>
			<thead>
				<tr>
					<th>{{ t('openconnector', 'Name') }}</th>
					<th>{{ t('openconnector', 'Description') }}</th>
					<th>{{ t('openconnector', 'Condition') }}</th>
					<th>{{ t('openconnector', 'Created') }}</th>
					<th>{{ t('openconnector', 'Status') }}</th>
					<th>{{ t('openconnector', 'Actions') }}</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="rule in rules"
					:key="rule.id">
					<td>
						<router-link :to="{ name: 'rule-detail', params: { id: rule.id }}">
							{{ rule.name }}
						</router-link>
					</td>
					<td>{{ rule.description }}</td>
					<td>{{ rule.condition }}</td>
					<td>{{ formatDate(rule.created) }}</td>
					<td>
						<NcBadge :type="rule.active ? 'success' : 'error'">
							{{ rule.active ? t('openconnector', 'Active') : t('openconnector', 'Inactive') }}
						</NcBadge>
					</td>
					<td class="actions">
						<NcActions>
							<NcActionButton @click="$router.push({ name: 'rule-detail', params: { id: rule.id }})">
								<template #icon>
									<Eye :size="20" />
								</template>
								{{ t('openconnector', 'View') }}
							</NcActionButton>
							<NcActionButton @click="$emit('edit', rule)">
								<template #icon>
									<Pencil :size="20" />
								</template>
								{{ t('openconnector', 'Edit') }}
							</NcActionButton>
							<NcActionButton @click="$emit('delete', rule)">
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
	name: 'RuleList',
	components: {
		NcActions,
		NcActionButton,
		NcBadge,
	},
	props: {
		rules: {
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
.rule-list {
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