<script setup>
import { sourceStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editSource'"
		ref="modalRef"
		label-id="editSource"
		@close="closeModal">
		<div class="modalContent">
			<h2>Bron {{ sourceItem.id ? 'Aanpassen' : 'Aanmaken' }}</h2>
			<NcNoteCard v-if="success" type="success">
				<p>Bron succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<form v-if="!success" @submit.prevent="handleSubmit">
				<div class="form-group">
					<NcTextField
						id="name"
						label="Naam*"
						:value.sync="sourceItem.name" />

					<NcTextArea
						id="description"
						label="Beschrijving"
						:value.sync="sourceItem.description" />

					<NcSelect
						id="type"
						v-bind="typeOptions"
						v-model="typeOptions.value" />

					<NcTextField
						id="location"
						label="location*"
						:value.sync="sourceItem.location" />
				</div>
			</form>

			<NcButton
				v-if="!success"
				:disabled="loading || !sourceItem.name || !sourceItem.location || !typeOptions.value"
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
	NcTextField,
	NcTextArea,
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
		NcTextField,
		NcTextArea,
	},
	data() {
		return {
			sourceItem: {
				name: '',
				description: '',
				type: '',
				location: '',
			},
			success: false,
			loading: false,
			error: false,
			typeOptions: {
				inputLabel: 'Type*',
				options: [
					{ label: 'Database', id: 'database' },
					{ label: 'API', id: 'api' },
					{ label: 'File', id: 'file' },
				],

			},
		}
	},
	methods: {
		closeModal() {
			navigationStore.setModal(false)
			this.succes = false
			this.loading = false
			this.error = false
			this.sourceItem = {
				name: '',
				description: '',
				type: '',
				connection: '',
			}
		},
		async editSource() {
			this.loading = true
			try {
				await sourceStore.saveSource({ ...this.sourceItem, type: this.typeOptions.value.id })
				// Close modal or show success message
				this.success = true
				this.loading = false
				setTimeout(this.closeModal, 2000)
			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'Er is een fout opgetreden bij het opslaan van de bron'
			}
		},
	},
}
</script>
