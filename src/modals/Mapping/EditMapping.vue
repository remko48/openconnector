<script setup>
import { mappingStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editMapping'"
		ref="modalRef"
		label-id="editMapping"
		@close="closeModal">
		<div class="modalContent">
			<h2>Mapping {{ mappingStore.mappingItem?.id ? 'Edit' : 'Add' }}</h2>

			<NcNoteCard v-if="success" type="success">
				<p>Mapping successfully added</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<form v-if="!success" @submit.prevent="handleSubmit">
				<div class="form-group">
					<NcTextField
						id="name"
						label="Name"
						:value.sync="mappingItem.name" />

					<NcTextArea
						id="description"
						label="Description"
						:value.sync="mappingItem.description" />

					<NcCheckboxRadioSwitch
						:checked.sync="mappingItem.passThrough">
						Pass Through
					</NcCheckboxRadioSwitch>
				</div>
			</form>

			<div v-if="!success" class="buttons">
				<NcButton :disabled="loading"
					type="primary"
					@click="editMapping()">
					<template #icon>
						<NcLoadingIcon v-if="loading" :size="20" />
						<ContentSaveOutline v-if="!loading" :size="20" />
					</template>
					Save
				</NcButton>
				<NcButton type="secondary"
					@click="openLink('https://commongateway.github.io/CoreBundle/pages/Features/Mappings', '_blank')">
					<template #icon>
						<BookOpenVariant :size="20" />
					</template>
					Documentation
				</NcButton>
			</div>
		</div>
	</NcModal>
</template>

<script>
import {
	NcButton,
	NcModal,
	NcLoadingIcon,
	NcNoteCard,
	NcTextField,
	NcTextArea,
	NcCheckboxRadioSwitch,
} from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import BookOpenVariant from 'vue-material-design-icons/BookOpenVariant.vue'

import openLink from '../../services/openLink.js'

export default {
	name: 'EditMapping',
	components: {
		NcModal,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
		NcTextField,
		NcTextArea,
		NcCheckboxRadioSwitch,
		// Icons
		ContentSaveOutline,
	},
	data() {
		return {
			mappingItem: {
				name: '',
				description: '',
				passThrough: true,
			},
			success: null,
			loading: false,
			error: false,
			hasUpdated: false,
			closeTimeoutFunc: null,
		}
	},
	mounted() {
		this.initializeMappingItem()
	},
	updated() {
		if (navigationStore.modal === 'editMapping' && !this.hasUpdated) {
			this.initializeMappingItem()
			this.hasUpdated = true
		}
	},
	methods: {
		initializeMappingItem() {
			if (mappingStore.mappingItem?.id) {
				this.mappingItem = {
					...mappingStore.mappingItem,
				}
			}
		},
		closeModal() {
			navigationStore.setModal(false)
			clearTimeout(this.closeTimeoutFunc)
			this.success = null
			this.loading = false
			this.error = false
			this.hasUpdated = false
			this.mappingItem = {
				id: null,
				name: '',
				description: '',
				passThrough: true,
			}
		},
		async editMapping() {
			this.loading = true

			mappingStore.saveMapping({
				...this.mappingItem,
			}).then(({ response }) => {
				this.success = response.ok
				this.closeTimeoutFunc = setTimeout(this.closeModal, 2000)
			}).catch((error) => {
				this.error = error.message || 'An error occurred while saving the mapping'
			}).finally(() => {
				this.loading = false
			})
		},
	},
}
</script>

<style scoped>
.buttons {
    display: flex;
    gap: 10px;
}
</style>
