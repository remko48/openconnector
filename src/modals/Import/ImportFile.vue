<script setup>
import { navigationStore, importExportStore, sourceStore, endpointStore, jobStore, mappingStore, synchronizationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'importFile'"
		ref="modalRef"
		label-id="ImportFileModal"
		@close="closeModal()">
		<div class="modal__content">
			<h2>Import {{ importExportStore.importFileName }}</h2>

			<div v-if="success !== null || error">
				<NcNoteCard v-if="success" type="success">
					<p>Successfully imported file</p>
				</NcNoteCard>
				<NcNoteCard v-if="!success" type="error">
					<p>Something went wrong while importing</p>
				</NcNoteCard>
				<NcNoteCard v-if="error && !success" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>
			<div v-if="success === null" class="form-group">
				<div class="addFileContainer">
					<div :ref="'dropZoneRef'" class="filesListDragDropNotice">
						<div class="filesListDragDropNoticeWrapper">
							<div class="filesListDragDropNoticeWrapperIcon">
								<TrayArrowDown :size="48" />
								<h3 class="filesListDragDropNoticeTitle">
									Drag and drop a file here
								</h3>
							</div>

							<h3 class="filesListDragDropNoticeTitle">
								Of
							</h3>

							<div class="filesListDragDropNoticeTitle">
								<NcButton v-if="success === null && !files"
									:disabled="loading"
									type="primary"
									@click="openFileUpload()">
									<template #icon>
										<Plus :size="20" />
									</template>
									Add file
								</NcButton>

								<NcButton v-if="success === null && files"
									:disabled=" loading"
									type="primary"
									@click="reset()">
									<template #icon>
										<Minus :size="20" />
									</template>
									<span v-for="file of files" :key="file.name">{{ file.name }}</span>
								</NcButton>
							</div>
						</div>
					</div>
				</div>
				<NcButton v-if="success === null"
					type="primary"
					:disabled="!files"
					@click="importFile()">
					<template #icon>
						<NcLoadingIcon v-if="loading" :size="20" />
						<Plus v-if="!loading" :size="20" />
					</template>
					Import
				</NcButton>
			</div>
		</div>
	</NcModal>
</template>

<script>
import { NcButton, NcLoadingIcon, NcModal, NcNoteCard } from '@nextcloud/vue'
import { useFileSelection } from '../../composables/UseFileSelection.js'

import { ref } from 'vue'

import Minus from 'vue-material-design-icons/Minus.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import TrayArrowDown from 'vue-material-design-icons/TrayArrowDown.vue'

import axios from 'axios'

const dropZoneRef = ref()
const { openFileUpload, files, reset, setFiles } = useFileSelection({ allowMultiple: false, dropzone: dropZoneRef })

export default {
	name: 'ImportFile',
	components: {
		NcModal,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
	},
	props: {
		dropFiles: {
			type: Array,
			required: false,
			default: null,
		},
	},
	data() {
		return {
			loading: false,
			success: null,
			error: false,
			labelOptions: {
				inputLabel: 'Labels',
				multiple: true,
				options: ['Besluit', 'Convenant', 'Document', 'Informatieverzoek', 'Inventarisatielijst'],
			},
		}
	},
	watch: {
		dropFiles: {
			handler(addedFiles) {
				importExportStore.importedFile && setFiles(addedFiles)
			},
			deep: true,
		},
	},
	mounted() {
		importExportStore.setImportedFile(null)
	},
	methods: {

		closeModal() {
			navigationStore.setModal(false)
			importExportStore.setImportedFile(null)
			importExportStore.setImportFileName('')
			reset()

		},
		importFile() {
			this.loading = true
			this.errorMessage = false

			axios.post('/index.php/apps/openconnector/api/import', {
				file: files.value ? files.value[0] : '',
			}, {
				headers: {
					'Content-Type': 'multipart/form-data',
				},
			}).then((response) => {

				this.success = true
				reset()

				const setItem = () => {
					switch (importExportStore.importFileName) {
					case 'source':
						return (
							sourceStore.refreshSourceList().then(() => {
								const source = sourceStore.sourceList.find(source => source.id === response.data.object.id)
								sourceStore.setSourceItem(source)
							})
						)
					case 'endpoint':
						return (
							endpointStore.refreshEndpointList().then(() => {
								const endpoint = endpointStore.endpointList.find(endpoint => endpoint.id === response.data.object.id)
								endpointStore.setEndpointItem(endpoint)
							})
						)
					case 'job':
						return (
							jobStore.refreshJobList().then(() => {
								const job = jobStore.jobList.find(job => job.id === response.data.object.id)
								jobStore.setJobItem(job)
							})
						)
					case 'mapping':
						return (
							mappingStore.refreshMappingList().then(() => {
								const mapping = mappingStore.mappingList.find(mapping => mapping.id === response.data.object.id)
								mappingStore.setMappingItem(mapping)
							})
						)
					case 'synchronization':
						return (
							synchronizationStore.refreshSynchronizationList().then(() => {
								const synchronization = synchronizationStore.synchronizationList.find(synchronization => synchronization.id === response.data.object.id)
								synchronizationStore.setSynchronizationItem(synchronization)
							})
						)
					}
				}

				setItem()
				// Wait for the user to read the feedback then close the model
				const self = this
				setTimeout(function() {
					self.success = null
					self.closeModal()
				}, 2000)

			})
				.catch((err) => {
					this.error = err.response?.data?.error ?? err
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

.addFileContainer{
	margin-block-end: var(--OC-margin-20);
}
.addFileContainer--disabled{
	opacity: 0.4;
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
