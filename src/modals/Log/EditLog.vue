<script setup>
import { logStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editLog'" ref="modalRef" @close="navigationStore.setModal(false)">
		<div class="modalContent">
			<h2>Log {{ logStore.logItem.id ? 'Aanpassen' : 'Aanmaken' }}</h2>
			<NcNoteCard v-if="success" type="success">
				<p>Log succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<form v-if="!success" @submit.prevent="handleSubmit">
				<div class="form-group">
					<label for="title">Title:</label>
					<input v-model="logStore.logItem.title" id="title" required>
				</div>
				<div class="form-group">
					<label for="description">Description:</label>
					<textarea v-model="logStore.logItem.description" id="description"></textarea>
				</div>
				<div class="form-group">
					<label for="date">Date:</label>
					<input type="date" v-model="logStore.logItem.date" id="date" required>
				</div>
			</form>

			<NcButton
				v-if="!success"
				:disabled="loading"
				type="primary"
				@click="editLog()">
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
	NcLoadingIcon,
	NcNoteCard,
} from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
	name: 'EditLog',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
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
		async editLog() {
			this.loading = true
			try {
				await logStore.saveLog()
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
				this.error = error.message || 'An error occurred while saving the log'
			}
		}
	},
}
</script>
