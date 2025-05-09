<script setup>
import { navigationStore, mappingStore } from '../../store/store.js'
import { Mapping } from '../../entities/index.js'
</script>

<template>
	<NcDialog
		v-if="navigationStore.modal === 'deleteMappingMapping'"
		name="Delete Mapping"
		:can-close="false">
		<div v-if="success !== null || error">
			<NcNoteCard v-if="success" type="success">
				<p>Successfully deleted mapping</p>
			</NcNoteCard>
			<NcNoteCard v-if="!success" type="error">
				<p>Something went wrong deleting the mapping</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>
		</div>
		<p v-if="success === null">
			Do you want to delete <b>{{ mappingStore.mappingMappingKey }}</b>? This action cannot be undone.
		</p>
		<template #actions>
			<NcButton :disabled="loading" icon="" @click="closeModal">
				<template #icon>
					<Cancel :size="20" />
				</template>
				{{ success !== null ? 'Close' : 'Cancel' }}
			</NcButton>
			<NcButton
				v-if="success === null"
				:disabled="loading"
				icon="Delete"
				type="error"
				@click="deleteMappingMapping()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<Delete v-if="!loading" :size="20" />
				</template>
				Delete
			</NcButton>
		</template>
	</NcDialog>
</template>

<script>
import { NcButton, NcDialog, NcNoteCard, NcLoadingIcon } from '@nextcloud/vue'

import Cancel from 'vue-material-design-icons/Cancel.vue'
import Delete from 'vue-material-design-icons/Delete.vue'

export default {
	name: 'DeleteMappingMapping',
	components: {
		NcDialog,
		NcButton,
		NcNoteCard,
		NcLoadingIcon,
		// Icons
		Cancel,
		Delete,
	},
	data() {
		return {
			loading: false,
			success: null,
			error: false,
			closeTimeoutFunc: null,
		}
	},
	methods: {
		closeModal() {
			navigationStore.setModal(false)
			clearTimeout(this.closeTimeoutFunc)
			this.success = null
		},
		deleteMappingMapping() {
			this.loading = true

			const mappingClone = { ...mappingStore.mappingItem }
			delete mappingClone?.mapping[mappingStore.mappingMappingKey]

			const mappingItem = new Mapping({
				...mappingStore.mappingItem,
			})

			mappingStore.saveMapping(mappingItem)
				.then(() => {
					this.loading = false
					this.success = true

					// Wait for the user to read the feedback then close the model
					this.closeTimeoutFunc = setTimeout(this.closeModal, 2000)
				})
				.catch((err) => {
					this.error = err
					this.loading = false
				})
		},
	},
}
</script>

<style>
.modal__content {
    margin: var(--OC-margin-50);
    text-align: center;
}

.zaakDetailsContainer {
    margin-block-start: var(--OC-margin-20);
    margin-inline-start: var(--OC-margin-20);
    margin-inline-end: var(--OC-margin-20);
}

.success {
    color: green;
}
</style>
