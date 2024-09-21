<script setup>
import { synchronizationStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editSynchronization'" ref="modalRef" @close="navigationStore.setModal(false)">
		<div class="modalContent">
			<h2>Synchronisatie {{ synchronizationStore.synchronizationItem.id ? 'Aanpassen' : 'Aanmaken' }}</h2>
			<NcNoteCard v-if="success" type="success">
				<p>Synchronisatie succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<form v-if="!success" @submit.prevent="handleSubmit">
				<div class="form-group">
					<label for="name">Naam:</label>
					<input id="name" v-model="synchronizationStore.synchronizationItem.name" required>
				</div>
				<div class="form-group">
					<label for="description">Beschrijving:</label>
					<textarea id="description" v-model="synchronizationStore.synchronizationItem.description" />
				</div>
				<div class="form-group">
					<label for="source">Bron:</label>
					<NcSelect id="source" v-model="synchronizationStore.synchronizationItem.source" :options="sourceOptions" />
				</div>
				<div class="form-group">
					<label for="target">Doel:</label>
					<NcSelect id="target" v-model="synchronizationStore.synchronizationItem.target" :options="targetOptions" />
				</div>
				<div class="form-group">
					<label for="schedule">Schema:</label>
					<input id="schedule" v-model="synchronizationStore.synchronizationItem.schedule" placeholder="Cron expressie">
				</div>
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
		}
	},
	methods: {
		async editSynchronization() {
			this.loading = true
			try {
				await synchronizationStore.saveSynchronization()
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
				this.error = error.message || 'Er is een fout opgetreden bij het opslaan van de synchronisatie'
			}
		},
	},
}
</script>
