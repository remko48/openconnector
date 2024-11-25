<script setup>
import { mappingStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="editMappingMapping"
		@close="closeModal">
		<div class="modalContent">
			<h2>{{ isEdit ? 'Edit' : 'Add' }} Mapping</h2>
			<NcNoteCard v-if="success" type="success">
				<p>Mapping successfully added</p>
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
						:error="checkIfKeyIsUnique(mappingItem.key)"
						:helper-text="checkIfKeyIsUnique(mappingItem.key) ? 'This key is already in use. Please choose a different key name.' : ''"
						:value.sync="mappingItem.key" />
					<NcTextField
						id="value"
						label="Value"
						:value.sync="mappingItem.value" />
				</div>
			</form>

			<NcButton
				v-if="!success"
				:disabled="loading || !mappingItem.key || checkIfKeyIsUnique(mappingItem.key)"
				type="primary"
				@click="editMapping()">
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
	name: 'EditMappingMapping',
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
			mappingItem: {
				key: '',
				value: '',
			},
			success: false,
			loading: false,
			error: false,
			hasUpdated: false,
			closeTimeoutFunc: null,
			isEdit: !!mappingStore.mappingMappingKey,
		}
	},
	mounted() {
		this.initializeMappingMapping()
	},
	updated() {
		if (navigationStore.modal === 'editMappingMapping' && !this.hasUpdated) {
			this.initializeMappingMapping()
			this.hasUpdated = true
		}
	},
	methods: {
		initializeMappingMapping() {
			if (!mappingStore.mappingMappingKey) {
				return
			}
			const mappingItem = Object.entries(mappingStore.mappingItem.mapping).find(([key]) => key === mappingStore.mappingMappingKey)
			if (mappingItem) {
				this.mappingItem = {
					key: mappingItem[0] || '',
					value: mappingItem[1] || '',
				}
			}
		},
		checkIfKeyIsUnique(key) {
			if (!mappingStore.mappingItem.mapping) return false
			const keys = Object.keys(mappingStore.mappingItem.mapping)
			if (mappingStore.mappingMappingKey === key) return false
			if (keys.includes(key)) return true
			return false
		},
		closeModal() {
			navigationStore.setModal(false)
			mappingStore.setMappingMappingKey(null)
			clearTimeout(this.closeTimeoutFunc)
		},
		async editMapping() {
			this.loading = true

			const newMappingItem = _.cloneDeep(mappingStore.mappingItem)
			newMappingItem.mapping[mappingStore.mappingMappingKey || this.mappingItem.key] = this.mappingItem.value

			if (mappingStore.mappingMappingKey && mappingStore.mappingMappingKey !== this.mappingItem.key) {
				newMappingItem.mapping = renameKey(newMappingItem.mapping, mappingStore.mappingMappingKey, this.mappingItem.key)
			}

			mappingStore.saveMapping(newMappingItem)
				.then(({ response }) => {
					this.success = response.ok
					this.closeTimeoutFunc = setTimeout(this.closeModal, 2000)
				})
				.catch((error) => {
					this.success = false
					this.error = error.message || 'An error occurred while saving the mapping'
				})
				.finally(() => {
					this.loading = false
				})
		},
	},
}
</script>
