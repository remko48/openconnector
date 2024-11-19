<script setup>
import { synchronizationStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editSynchronization'"
		ref="modalRef"
		label-id="editSynchronization"
		@close="closeModal">
		<div class="modalContent">
			<h2>{{ synchronizationItem.id ? 'Edit' : 'Add' }} Synchronization</h2>

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
					label="sourceId" />

				<NcTextField :value.sync="synchronizationItem.sourceType"
					label="sourceType" />

				<NcTextField :value.sync="synchronizationItem.sourceConfig.idPosition"
					label="(optional) Position of id in source object" />

				<NcTextField :value.sync="synchronizationItem.sourceConfig.resultsPosition"
					label="(optional) Position of results in source object" />

				<NcTextField :value.sync="synchronizationItem.sourceConfig.endpoint"
					label="(optional) Endpoint on which to fetch data" />

				<NcTextField :value.sync="synchronizationItem.sourceTargetMapping"
					label="sourceTargetMapping" />

				<NcTextField :value.sync="synchronizationItem.targetId"
					label="targetId" />

				<NcTextField :value.sync="synchronizationItem.targetType"
					label="targetType" />

				<NcTextField :value.sync="synchronizationItem.targetSourceMapping"
					label="targetSourceMapping" />
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
				sourceConfig: {
					idPosition: '',
					resultsPosition: '',
					endpoint: '',
					headers: {},
					query: {},
				},
				sourceTargetMapping: '',
				targetId: '',
				targetType: 'register/schema',
				targetConfig: {},
				targetSourceMapping: '',
			}, // Initialize with empty fields
			hasUpdated: false, // Flag to prevent constant looping
			sourceLoading: false,
			closeTimeoutFunc: null,
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
			clearTimeout(this.closeTimeoutFunc)
			navigationStore.setModal(false)
			this.synchronizationItem = {
				name: '',
				description: '',
				sourceId: '',
				sourceType: 'api',
				sourceConfig: {
					idPosition: '',
					resultsPosition: '',
					endpoint: '',
					headers: {},
					query: {},
				},
				sourceTargetMapping: '',
				targetId: '',
				targetType: 'register/schema',
				targetConfig: {},
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
				this.closeTimeoutFunc = setTimeout(this.closeModal, 2000)
			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'Er is een fout opgetreden bij het opslaan van de synchronisatie'
			}
		},
	},
}
</script>
