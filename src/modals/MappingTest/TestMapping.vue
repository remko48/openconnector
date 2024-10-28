<script setup>
import { mappingStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="testMapping"
		@close="closeModal">
		<!-- Do not remove this seemingly useless class "TestMappingMainModal" -->
		<div class="modalContent TestMappingMainModal">
			<h2>Mapping test</h2>

			<div class="content">
				<TestMappingInputObject :input-object="inputObject"
					@input-object-changed="(value) => inputObject = value" />
				<div />
				<div />
			</div>
		</div>
	</NcModal>
</template>

<script>
import {
	NcModal,
	NcSelect,
	NcTextArea,
	NcButton,
	NcLoadingIcon,
	NcNoteCard,
	NcGuestContent,
} from '@nextcloud/vue'
import TestMappingInputObject from './components/TestMappingInputObject.vue'

import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import SitemapOutline from 'vue-material-design-icons/SitemapOutline.vue'
import FileTreeOutline from 'vue-material-design-icons/FileTreeOutline.vue'
import CloudDownload from 'vue-material-design-icons/CloudDownload.vue'
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import ContentSave from 'vue-material-design-icons/ContentSave.vue'

import openLink from '../../services/openLink.js'

import { Mapping } from '../../entities/index.js'

export default {
	name: 'TestMapping',
	components: {
		NcModal,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
		NcSelect,
		NcTextArea,
		NcGuestContent,
		// Icons
		ContentSaveOutline,
	},
	data() {
		return {
			// default data
			inputObject: '',
			mappingItem: null,
			// rest
			result: {}, // result from the testMapping function
			success: null,
			loading: false,
			error: false,
		}
	},
	mounted() {
		this.fetchMappings()
	},
	methods: {
		fetchMappings(currentMappingItem = null) {
			this.mappingsLoading = true

			mappingStore.refreshMappingList()
				.then(() => {
					if (currentMappingItem) {
						currentMappingItem = mappingStore.mappingItem || null
					}

					const selectedMapping = mappingStore.mappingList.find((mapping) => mapping.id === (currentMappingItem?.id || Symbol('mapping item id not found')))

					const fallbackMapping = mappingStore.mappingList[0]
						? {
							id: mappingStore.mappingList[0].id,
							label: mappingStore.mappingList[0].name,
							summary: mappingStore.mappingList[0].description,
							fullMapping: mappingStore.mappingList[0],
						}
						: null

					this.mappings = {
						options: [
							{
								id: 'no-mapping',
								label: 'No mapping',
								summary: 'No mapping available',
								fullMapping: null,
								removeStyle: true,
							},
							...mappingStore.mappingList.map((mapping) => ({
								id: mapping.id,
								label: mapping.name,
								summary: mapping.description,
								fullMapping: mapping,
							})),
						],
						value: selectedMapping
							? {
								id: selectedMapping.id,
								label: selectedMapping.name,
								summary: selectedMapping.description,
								fullMapping: selectedMapping,
							}
							: fallbackMapping,
					}
				})
				.finally(() => {
					this.mappingsLoading = false
				})
		},
		closeModal() {
			navigationStore.setModal(false)
		},
		async testMapping() {
			this.loading = true
			this.success = null
			this.result = {}

			mappingStore.testMapping({
				mapping: this.editMapping
					? { // apply the edited mapping to the full mapping
						...this.mappings.value.fullMapping,
						mapping: JSON.parse(this.mapping),
						cast: JSON.parse(this.cast),
					} // use the full mapping as is
					: this.mappings.value.fullMapping,
				inputObject: this.inputObject,
				schema: this.schemas.value?.fullSchema,
			})
				.then(({ response, data }) => {
					this.success = response.ok
					this.result = data
				}).catch((error) => {
					this.error = error.message || 'An error occurred while testing the mapping'
				}).finally(() => {
					this.loading = false
				})
		},
		validJson(object) {
			try {
				JSON.parse(object)
				return true
			} catch (e) {
				return false
			}
		},
	},
}
</script>

<style>
/* modal */
div[class='modal-container']:has(.TestMappingMainModal) {
    /* width: 90vw !important; */
}
</style>

<style scoped>
.buttons {
    display: flex;
    gap: 10px;
}

.flex-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.textarea :deep(textarea) {
    resize: vertical !important;
}

/* Mapping edit */
.mapping-edit-container {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 10px;
}
.mapping-edit-container .button-vue {
    margin-top: 4px;
}

.edit-mapping-textarea :deep(textarea) {
    height: 150px;
}

/* Mapping option */
.mapping-option {
    display: flex;
    align-items: center;
    gap: 10px;
}
.mapping-option > .material-design-icon {
    margin-top: 2px;
}
.mapping-option > h6 {
    line-height: 0.8;
}

/* install OR buttons */
.notecard {
    text-align: left;
}
.install-buttons {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}
</style>
