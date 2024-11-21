<script setup>
import { sourceStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="editSourceConfigurationAuthentication"
		@close="closeModal">
		<div class="modalContent">
			<h2>{{ isEdit ? 'Edit' : 'Add' }} Authentication</h2>
			<NcNoteCard v-if="success" type="success">
				<p>Authentication successfully added</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<form v-if="!success" @submit.prevent="handleSubmit">
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

			<NcButton
				v-if="!success"
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
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

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
			success: false,
			loading: false,
			error: false,
			hasUpdated: false,
			closeTimeoutFunc: null,
			oldKey: '',
			isEdit: false,
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
				this.oldKey = configurationItem[0]
				this.isEdit = true
			}
		},
		checkIfKeyIsUnique(key) {
			if (!sourceStore.sourceItem.configuration) return false
			const fullKey = `authentication.${key}`
			const keys = Object.keys(sourceStore.sourceItem.configuration)
			if (this.oldKey === fullKey) return false
			if (keys.includes(fullKey)) return true
			return false
		},
		closeModal() {
			navigationStore.setModal(false)
			clearTimeout(this.closeTimeoutFunc)
			sourceStore.setSourceConfigurationKey(null)
		},
		async editSourceConfiguration() {
			this.loading = true

			const newSourceItem = {
				...sourceStore.sourceItem,
				configuration: {
					...sourceStore.sourceItem.configuration,
					[`authentication.${this.configurationItem.key}`]: this.configurationItem.value,
				},
			}

			if (this.oldKey !== '' && this.oldKey !== `authentication.${this.configurationItem.key}`) {
				delete newSourceItem.configuration[this.oldKey]
			}

			try {
				await sourceStore.saveSource(newSourceItem)
				// Close modal or show success message
				this.success = true
				this.loading = false
				this.closeTimeoutFunc = setTimeout(this.closeModal, 2000)
			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'An error occurred while saving the authentication configuration'
			}
		},
	},
}
</script>
