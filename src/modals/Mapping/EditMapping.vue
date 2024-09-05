<script setup>
import { mappingStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editMapping'" ref="modalRef" @close="navigationStore.setModal(false)">
		<div class="modalContent">
			<h2>Mapping {{ mappingStore.mappingItem.id ? 'Aanpassen' : 'Aanmaken' }}</h2>
			<NcNoteCard v-if="success" type="success">
				<p>Mapping succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<form v-if="!success" @submit.prevent="handleSubmit">
				<div class="form-group">
					<label for="name">Name:</label>
					<input v-model="mappingStore.mappingItem.name" id="name" required>
				</div>
				<div class="form-group">
					<label for="description">Description:</label>
					<textarea v-model="mappingStore.mappingItem.description" id="description"></textarea>
				</div>
				<div class="form-group">
					<label for="sourceField">Source Field:</label>
					<input v-model="mappingStore.mappingItem.sourceField" id="sourceField" required>
				</div>
				<div class="form-group">
					<label for="targetField">Target Field:</label>
					<input v-model="mappingStore.mappingItem.targetField" id="targetField" required>
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
				Opslaan
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import {
	NcButton,
	NcModal,
	NcTextField,
	NcTextArea,
	NcSelect,
	NcLoadingIcon,
	NcNoteCard,
} from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
	name: 'EditMapping',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
		NcSelect,
		NcLoadingIcon,
		NcNoteCard,
		// Icons
		ContentSaveOutline,
	},
	data() {
		return {
			success: false,
			loading: false,
			error: false,
		}
	},
	methods: {
		async editMapping() {
			this.loading = true
			try {
				await mappingStore.saveMapping()
				// Close modal or show success message
				this.success = true
				this.loading = false
				setTimeout(() => {
					this.success = false
					this.loading = false
					this.error = false
					navigationStore.setModal(false)
				}, 2000)
			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'An error occurred while saving the mapping'
			}
		}
	},
}
</script>
