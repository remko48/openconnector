<script setup>
import { synchronizationStore, navigationStore, sourceStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editSynchronization'"
		ref="modalRef"
		label-id="editSynchronization"
		@close="closeModal">
		<div class="modalContent">
			<h2>Synchronization{{ synchronizationItem.id ? 'Edit' : 'Add' }}</h2>

			<NcNoteCard v-if="success" type="success">
				<p>Synchronization successfully added</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<form v-if="!success" @submit.prevent="handleSubmit">
				<NcTextField :value.sync="synchronizationItem.name"
					label="Name"
					required />

				<NcTextArea :value.sync="synchronizationItem.description"
					label="Description" />

				<NcTextField :value.sync="synchronizationItem.sourceId"
					label="sourceId"
					required />

				<NcTextField :value.sync="synchronizationItem.sourceType"
					label="sourceType"
					required />

				<NcTextField :value.sync="synchronizationItem.sourceTargetMapping"
					label="sourceTargetMapping"
					required />

				<NcTextField :value.sync="synchronizationItem.targetId"
					label="targetId"
					required />

				<NcTextField :value.sync="synchronizationItem.targetType"
					label="targetType"
					required />

				<NcTextField :value.sync="synchronizationItem.targetSourceMapping"
					label="targetSourceMapping"
					required />
			</form>

			<NcButton
				v-if="!success"
				:disabled="loading || !synchronizationItem.name"
				type="primary"
				@click="editSynchronization()">
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
	NcSelect,
	NcLoadingIcon,
	NcNoteCard,
} from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
	name: 'EditSynchronization',
	components: {
		NcModal,
		NcButton,
		NcTextField,
		NcTextArea,
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
			sourceOptions: [], // This should be populated with available sources
			source: {},
			targetOptions: [], // This should be populated with available targets
			synchronizationItem: {
				name: '',
				description: '',
				sourceId: '',
				sourceType: 'api',
				sourceTargetMapping: '',
				targetId: '',
				targetType: 'register/schema',
				targetSourceMapping: '',
			}, // Initialize with empty fields
			hasUpdated: false, // Flag to prevent constant looping
			sourceLoading: false,
		}
	},
	updated() {
		if (navigationStore.modal === 'editSynchronization' && !this.hasUpdated) {
			synchronizationStore.synchronizationItem && (this.synchronizationItem = { ...synchronizationStore.synchronizationItem })
			this.hasUpdated = true
		}
	},
	methods: {
		closeModal() {
			this.success = false
			this.loading = false
			this.error = false
			this.hasUpdated = false
			navigationStore.setModal(false)
			this.synchronizationItem = {
				name: '',
				description: '',
				sourceId: '',
				sourceType: 'api',
				sourceTargetMapping: '',
				targetId: '',
				targetType: 'register/schema',
				targetSourceMapping: '',
			}
		},
		async editSynchronization() {
			this.loading = true
			try {
				await synchronizationStore.saveSynchronization({
					...this.synchronizationItem,
				})
				this.success = true
				this.loading = false

				setTimeout(this.closeModal, 2000)
			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'Er is een fout opgetreden bij het opslaan van de synchronisatie'
			}
		},
	},
}
</script>
