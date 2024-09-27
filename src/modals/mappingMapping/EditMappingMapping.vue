<script setup>
import { mappingStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editMappingMapping'"
		ref="modalRef"
		label-id="editMappingMapping"
		@close="closeModal">
		<div class="modalContent">
			<h2>{{ mappingItem?.id ? 'Edit' : 'Add' }} Mapping</h2>
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
						label="Key"
						:value.sync="mappingItem.key" />
					<NcTextField
						id="value"
						label="Value"
						:value.sync="mappingItem.value" />
				</div>
			</form>

			<NcButton
				v-if="!success"
				:disabled="loading"
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
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

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
		}
	},
	methods: {
		closeModal() {
			navigationStore.setModal(false)
			this.success = false
			this.loading = false
			this.error = false
			this.mappingItem = {
				key: '',
				value: '',
			}
		},
		async editMapping() {
			this.loading = true

			const newMappingItem = {
				...mappingStore.mappingItem,
				mapping: {
					...mappingStore.mappingItem.mapping,
					[this.mappingItem.key]: this.mappingItem.value,
				},
			}

			try {
				await mappingStore.saveMapping(newMappingItem)
				// Close modal or show success message
				this.success = true
				this.loading = false
				setTimeout(() => {
					this.closeModal()
				}, 2000)
			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'An error occurred while saving the mapping'
			}
		},
	},
}
</script>
