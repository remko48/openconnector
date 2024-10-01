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

				<NcSelect
					v-bind="source"
					v-model="source.value"
					:disabled="sourceLoading || loading"
					input-label="Source*" />

				<NcSelect
					v-bind="target"
					v-model="target.value"
					:disabled="targetLoading || loading"
					input-label="Target*" />
			</form>

			<NcButton
				v-if="!success"
				:disabled="loading || !synchronizationItem.name || !source.value || !target.value"
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
			sourceLoading: false,
		}
	},
	mounted() {
		this.fetchSources()
		this.fetchTargets()
	},
	updated() {
		if (navigationStore.modal === 'editSynchronization' && !this.hasUpdated) {
			synchronizationStore.synchronizationItem && (this.synchronizationItem = { ...synchronizationStore.synchronizationItem })
			this.hasUpdated = true
			this.fetchSources()
			this.fetchTargets()
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
				await synchronizationStore.saveSynchronization({
					...this.synchronizationItem,
					sourceId: this.source.value.id,
					sourceType: this.source.value.type,
					sourceHash: this.source.value.hash,
					sourceTargetMapping: this.source.value.mapping,
					sourceConfig: this.source.value.configuration,
					sourceLastSync: this.source.value.lastSync === null ? null : this.source.value.lastSync.date,
					sourceLastChanged: this.source.value.dateModified === null ? null : this.source.value.dateModified.date,
					sourceLastChecked: this.source.value.lastCall === null ? null : this.source.value.lastCall.date,
					targetId: this.target.value.id,
					targetType: this.target.value.type,
					targetHash: this.target.value.hash,
					targetSourceMapping: this.target.value.mapping,
					targetConfig: this.target.value.configuration,
					targetLastSync: this.target.value.lastSync === null ? null : this.target.value.lastSync.date,
					targetLastChanged: this.target.value.dateModified === null ? null : this.target.value.dateModified.date,
					targetLastChecked: this.target.value.lastCall === null ? null : this.target.value.lastCall.date,
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
		fetchSources() {
			this.sourceLoading = true

			sourceStore.refreshSourceList()
				.then(() => {

					this.sourceOptions = sourceStore.sourceList

					this.source = {
						options: Object.entries(sourceStore.sourceList).map((source) => ({
							id: source[1].id,
							label: source[1].name,
							type: source[1].type,
							hash: source[1].hash,
							mapping: source[1].mapping,
							configuration: source[1].configuration,
							dateModified: source[1].dateModified,
							lastSync: source[1].lastSync,
							lastCall: source[1].lastCall,
						})),
						value: this.synchronizationItem.source,

					}

					this.sourceLoading = false
				})
				.catch((err) => {
					console.error(err)
					this.sourceLoading = false
				})
		},
		fetchTargets() {
			this.targetLoading = true

			sourceStore.refreshSourceList()
				.then(() => {

					this.targetOptions = sourceStore.sourceList

					this.target = {
						options: Object.entries(sourceStore.sourceList).map((target) => ({
							id: target[1].id,
							label: target[1].name,
							type: target[1].type,
							hash: target[1].hash,
							mapping: target[1].mapping,
							configuration: target[1].configuration,
							dateModified: target[1].dateModified,
							lastSync: target[1].lastSync,
							lastCall: target[1].lastCall,
						})),

					}

					this.targetLoading = false
				})
				.catch((err) => {
					console.error(err)
					this.sourceLoading = false
				})
		},
	},
}
</script>
