<script setup>
import { jobStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editJob'" ref="modalRef" @close="navigationStore.setModal(false)">
		<div class="modalContent">
			<h2>Job {{ jobStore.jobItem.id ? 'Aanpassen' : 'Aanmaken' }}</h2>
			<NcNoteCard v-if="success" type="success">
				<p>Job succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<form v-if="!success" @submit.prevent="handleSubmit">
				<div class="form-group">
					<NcTextField
						label="Title"
						maxlength="255"
						:value.sync="jobStore.jobItem.title"
						required />
				</div>
				<div class="form-group">
					<NcTextArea
						label="Description"
						:value.sync="jobStore.jobItem.description" />
				</div>
				<div class="form-group">
					<label for="status">Status:</label>
					<NcSelect id="status" v-model="jobStore.jobItem.status" :options="statusOptions" />
				</div>
			</form>

			<NcButton
				v-if="!success"
				:disabled="loading"
				type="primary"
				@click="editJob()">
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
	name: 'EditJob',
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
			statusOptions: [
				{ label: 'Open', value: 'open' },
				{ label: 'In Progress', value: 'in_progress' },
				{ label: 'Completed', value: 'completed' },
			],
		}
	},
	methods: {
		async editJob() {
			this.loading = true
			try {
				await jobStore.saveJob()
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
				this.error = error.message || 'An error occurred while saving the job'
			}
		},
	},
}
</script>
