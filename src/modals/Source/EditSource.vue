<script setup>
import { sourceStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editSource'" ref="modalRef" @close="navigationStore.setModal(false)">
		<div class="modalContent">
			<h2>Bron {{ sourceStore.sourceItem.id ? 'Aanpassen' : 'Aanmaken' }}</h2>
			<NcNoteCard v-if="success" type="success">
				<p>Bron succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<form v-if="!success" @submit.prevent="handleSubmit">
				<div class="form-group">
					<label for="name">Naam:</label>
					<input id="name" v-model="sourceStore.sourceItem.name" required>
				</div>
				<div class="form-group">
					<label for="description">Beschrijving:</label>
					<textarea id="description" v-model="sourceStore.sourceItem.description" />
				</div>
				<div class="form-group">
					<label for="type">Type:</label>
					<NcSelect id="type" v-model="sourceStore.sourceItem.type" :options="typeOptions" />
				</div>
				<div class="form-group">
					<label for="connection">Verbinding:</label>
					<input id="connection" v-model="sourceStore.sourceItem.connection" required>
				</div>
			</form>

			<NcButton
				v-if="!success"
				:disabled="loading"
				type="primary"
				@click="editSource()">
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
	name: 'EditSource',
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
			typeOptions: [
				{ label: 'Database', value: 'database' },
				{ label: 'API', value: 'api' },
				{ label: 'File', value: 'file' },
			],
		}
	},
	methods: {
		async editSource() {
			this.loading = true
			try {
				await sourceStore.saveSource()
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
				this.error = error.message || 'Er is een fout opgetreden bij het opslaan van de bron'
			}
		},
	},
}
</script>
