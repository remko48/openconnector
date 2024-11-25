<script setup>
import { sourceStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="editSourceConfigurationAuthentication"
		@close="closeModal">
		<div class="modalContent">
			<h2>{{ isEdit ? 'Edit' : 'Add' }} Authentication</h2>

			<div v-if="success !== null">
				<NcNoteCard v-if="success" type="success">
					<p>Authentication successfully added</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>

			<form v-if="success === null" @submit.prevent="handleSubmit">
				<div class="form-group">
					<NcTextField
						id="key"
						label="Key*"
						required
						:error="checkIfKeyIsUnique(configurationItem.key)"
						:helper-text="checkIfKeyIsUnique(configurationItem.key) ? 'This key is already in use. Please choose a different key name.' : ''"
						:value.sync="configurationItem.key" />
					<NcTextField
						id="value"
						label="Value"
						:value.sync="configurationItem.value" />
				</div>
			</form>

			<NcButton v-if="success === null"
				:disabled="loading || !configurationItem.key || checkIfKeyIsUnique(configurationItem.key)"
				type="primary"
				@click="editSourceConfiguration()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading" :size="20" />
				</template>
				Save
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import {
	NcButton,
	NcModal,
	NcLoadingIcon,
	NcNoteCard,
	NcTextField,
} from '@nextcloud/vue'
import _ from 'lodash'

import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

import renameKey from '../../services/renameKeyInObject.js'

export default {
	name: 'EditSourceConfigurationAuthentication',
	components: {
		NcModal,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
		NcTextField,
		// Icons
		ContentSaveOutline,
	},
	data() {
		return {
			configurationItem: {
				key: '',
				value: '',
			},
			success: null,
			loading: false,
			error: false,
			closeTimeoutFunc: null,
			isEdit: !!sourceStore.sourceConfigurationKey,
		}
	},
	mounted() {
		this.initializeSourceConfiguration()
	},
	methods: {
		initializeSourceConfiguration() {
			if (!sourceStore.sourceConfigurationKey) {
				return
			}
			const configurationItem = Object.entries(sourceStore.sourceItem.configuration).find(([key]) => key === sourceStore.sourceConfigurationKey)
			if (configurationItem) {
				this.configurationItem = {
					key: configurationItem[0].replace(/^authentication\./g, '') || '',
					value: configurationItem[1] || '',
				}
			}
		},
		checkIfKeyIsUnique(key) {
			if (!sourceStore.sourceItem.configuration) return false
			const fullKey = `authentication.${key}`
			const keys = Object.keys(sourceStore.sourceItem.configuration)
			if (sourceStore.sourceConfigurationKey === fullKey) return false
			if (keys.includes(fullKey)) return true
			return false
		},
		closeModal() {
			navigationStore.setModal(false)
			sourceStore.setSourceConfigurationKey(null)
			clearTimeout(this.closeTimeoutFunc)
		},
		async editSourceConfiguration() {
			this.loading = true

			const oldKey = sourceStore.sourceConfigurationKey
			const newKey = `authentication.${this.configurationItem.key}`

			const newSourceItem = _.cloneDeep(sourceStore.sourceItem)
			newSourceItem.configuration[newKey] = this.configurationItem.value

			if (this.isEdit && oldKey !== newKey) {
				newSourceItem.configuration = renameKey(newSourceItem.configuration, oldKey, newKey)
			}

			sourceStore.saveSource(newSourceItem)
				.then(({ response }) => {
					this.success = response.ok
					this.closeTimeoutFunc = setTimeout(this.closeModal, 2000)
				})
				.catch((error) => {
					this.success = false
					this.error = error.message || 'An error occurred while saving the source configuration'
				})
				.finally(() => {
					this.loading = false
				})
		},
	},
}
</script>
