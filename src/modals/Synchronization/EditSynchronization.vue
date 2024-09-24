<script setup>
import { synchronizationStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editSynchronization'"
		ref="modalRef"
		label-id="editSynchronization"
		@close="closeModal">
		<div class="modalContent">
			<h2>Synchronisatie {{ synchronizationItem.id ? 'Aanpassen' : 'Aanmaken' }}</h2>

			<NcNoteCard v-if="success" type="success">
				<p>Synchronisatie succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<form v-if="!success" @submit.prevent="handleSubmit">
				<NcTextField :value.sync="synchronizationItem.name"
					label="Naam"
					required />

				<NcTextArea :value.sync="synchronizationItem.description"
					label="Beschrijving" />

				<NcSelect v-model="synchronizationItem.source"
					input-label="Bron"
					:options="sourceOptions" />

				<NcSelect v-model="synchronizationItem.target"
					input-label="Doel"
					:options="targetOptions" />

				<NcTextField :value.sync="synchronizationItem.schedule"
					label="Schema"
					placeholder="Cron expressie" />
			</form>

			<NcButton
				v-if="!success"
				:disabled="loading"
				type="primary"
				@click="editSynchronization()">
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
			targetOptions: [], // This should be populated with available targets
			synchronizationItem: {
				name: '',
				description: '',
				source: '',
				target: '',
				schedule: '',
				entity: '',
				object: '',
				action: '',
				gateway: '',
				sourceObject: '',
			}, // Initialize with empty fields
			hasUpdated: false, // Flag to prevent constant looping
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
				source: '',
				target: '',
				schedule: '',
				entity: '',
				object: '',
				action: '',
				gateway: '',
				sourceObject: '',
			}
		},
		async editSynchronization() {
			this.loading = true
			try {
				await synchronizationStore.saveSynchronization(this.synchronizationItem)
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
