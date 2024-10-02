<script setup>
import { jobStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editJob'"
		ref="modalRef"
		label-id="editJob"
		@close="closeModal">
		<div class="modalContent">
			<h2>{{ jobItem?.id ? 'Edit' : 'Add' }} job</h2>
			<NcNoteCard v-if="success" type="success">
				<p>Successfully added job</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<form v-if="!success" @submit.prevent="handleSubmit">
				<div class="form-group">
					<NcTextField
						label="Name"
						maxlength="255"
						:value.sync="jobItem.name"
						required />
				</div>
				<div class="form-group">
					<NcTextArea
						label="Description"
						:value.sync="jobItem.description" />
				</div>
				<div class="form-group">
					<NcTextArea
						label="Job Class"
						:value.sync="jobStore.jobItem.jobClass" />
				</div>
				<div class="form-group">
					<NcInputField
						type="number"
						label="Intraval"
						:value.sync="jobItem.interval" />
				</div>
				<div class="form-group">
					<NcInputField
						type="number"
						label="errorRetention"
						:value.sync="jobStore.jobItem.errorRetention" />
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
				Save
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
	NcInputField,
} from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
	name: 'EditJob',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
		NcInputField,
		// Icons
		ContentSaveOutline,
	},
	data() {
		return {
			jobItem: {
				name: '',
				description: '',
				interval: '3600',
			},
			success: false,
			loading: false,
			error: false,
			statusOptions: [
				{ label: 'Open', value: 'open' },
				{ label: 'In Progress', value: 'in_progress' },
				{ label: 'Completed', value: 'completed' },
			],
			hasUpdated: false,
		}
	},
	mounted() {
		this.initializeJobItem()
	},
	updated() {
		if (navigationStore.modal === 'editJob' && !this.hasUpdated) {
			this.initializeJobItem()
			this.hasUpdated = true
		}
	},
	methods: {
		initializeJobItem() {
			if (jobStore.jobItem?.id) {
				this.jobItem = {
					...jobStore.jobItem,
					name: jobStore.jobItem.name || '',
					description: jobStore.jobItem.description || '',
					interval: jobStore.jobItem.interval || '3600',
				}
			}
		},
		closeModal() {
			navigationStore.setModal(false)
			this.success = false
			this.loading = false
			this.error = false
			this.hasUpdated = false
			this.jobItem = {
				name: '',
				description: '',
				interval: '3600',
			}
		},
		async editJob() {
			this.loading = true
			try {
				await jobStore.saveJob({ ...this.jobItem })
				// Close modal or show success message
				this.success = true
				this.loading = false
				setTimeout(this.closeModal, 2000)

			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'An error occurred while saving the job'
			}
		},
	},
}
</script>
