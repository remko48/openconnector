<script setup>
import { mappingStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="testMapping"
		@close="closeModal">
		<div class="modalContent">
			<h2>Mapping test</h2>

			<NcNoteCard v-if="!isOpenRegisterInstalled"
				:type="!noOpenRegisterToInstall ? 'info' : 'error'"
				:heading="!noOpenRegisterToInstall ? 'Open Register is not installed' : 'Failed to install Open Register'">
				<p>
					{{ !noOpenRegisterToInstall
						? 'Some features require Open Register to be installed'
						: 'This either means that Open Register is not available on this server or you need to confirm your password' }}
				</p>

				<div class="install-buttons">
					<NcButton v-if="!noOpenRegisterToInstall"
						aria-label="Install OpenRegister"
						size="small"
						type="primary"
						@click="installOpenRegister">
						<template #icon>
							<CloudDownload :size="20" />
						</template>
						Install OpenRegister
					</NcButton>
					<NcButton
						aria-label="Install OpenRegister Manually"
						size="small"
						type="secondary"
						@click="openLink('/index.php/settings/apps/organization/openregister', '_blank')">
						<template #icon>
							<OpenInNew :size="20" />
						</template>
						Install OpenRegister Manually
					</NcButton>
				</div>
			</NcNoteCard>

			<form @submit.prevent="handleSubmit">
				<div class="form-group">
					<div class="mapping-edit-container">
						<NcSelect v-bind="mappings"
							v-model="mappings.value"
							input-label="Mapping"
							:clearable="false"
							:loading="mappingsLoading || loading"
							required>
							<!-- eslint-disable-next-line vue/no-unused-vars vue/no-template-shadow  -->
							<template #no-options="{ search, searching, loading }">
								<p v-if="loading">
									Loading...
								</p>
								<p v-if="!loading && !mappings.options.length">
									Er zijn geen mappings beschikbaar
								</p>
							</template>
							<!-- eslint-disable-next-line vue/no-unused-vars  -->
							<template #option="{ id, label, summary, removeStyle }">
								<div :class="removeStyle !== true && 'mapping-option'">
									<!-- custom style is enabled -->
									<SitemapOutline v-if="!removeStyle" :size="25" />
									<span v-if="!removeStyle">
										<h6 style="margin: 0">
											{{ label }}
										</h6>
										{{ summary }}
									</span>
									<!-- custom style is disabled -->
									<p v-if="removeStyle">
										{{ label }}
									</p>
								</div>
							</template>
						</NcSelect>

						<NcButton
							aria-label="Edit mapping"
							:disabled="customMapping"
							size="normal"
							:type="editMapping ? 'primary' : 'secondary'"
							@click="editMapping = !editMapping">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Toggle edit mapping
						</NcButton>
					</div>

					<NcTextArea v-if="editMapping || customMapping"
						class="edit-mapping-textarea"
						:label="customMapping ? 'Create Mapping' : 'Edit Mapping'"
						:value.sync="mapping"
						:error="!validJson(mapping)"
						:error-message="!validJson(mapping) ? 'Invalid JSON' : ''" />

					<NcTextArea
						label="Input object"
						:value.sync="inputObject"
						:error="!validJson(inputObject)"
						:error-message="!validJson(inputObject) ? 'Invalid JSON' : ''" />

					<NcSelect v-if="isOpenRegisterInstalled"
						v-bind="schemas"
						v-model="schemas.value"
						input-label="Schema"
						:clearable="false"
						:loading="schemasLoading || loading"
						required>
						<!-- eslint-disable-next-line vue/no-unused-vars vue/no-template-shadow  -->
						<template #no-options="{ search, searching, loading }">
							<p v-if="loading">
								Loading...
							</p>
							<p v-if="!loading && !schemas.options.length">
								Er zijn geen schemas beschikbaar
							</p>
						</template>
						<!-- eslint-disable-next-line vue/no-unused-vars  -->
						<template #option="{ id, label, summary }">
							<div class="mapping-option">
								<FileTreeOutline :size="25" />
								<span>
									<h6 style="margin: 0">
										{{ label }}
									</h6>
									{{ summary }}
								</span>
							</div>
						</template>
					</NcSelect>
				</div>
			</form>

			<div class="buttons">
				<NcButton :disabled="loading || !mappings.value || !inputObject || !validJson(mapping) || !validJson(inputObject)"
					type="primary"
					@click="testMapping()">
					<template #icon>
						<NcLoadingIcon v-if="loading" :size="20" />
						<ContentSaveOutline v-if="!loading" :size="20" />
					</template>
					Test
				</NcButton>
			</div>

			<NcNoteCard v-if="success" type="success">
				<p>Mapping successfully tested</p>
			</NcNoteCard>
			<NcNoteCard v-if="success === false" type="error">
				<p>Mapping failed to test</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<div v-if="success" style="text-align: left">
				<NcGuestContent>
					<pre><!-- do NOT remove this comment
						-->{{ JSON.stringify(result, null, 2) }}
					</pre>
				</NcGuestContent>
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

import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import SitemapOutline from 'vue-material-design-icons/SitemapOutline.vue'
import FileTreeOutline from 'vue-material-design-icons/FileTreeOutline.vue'
import CloudDownload from 'vue-material-design-icons/CloudDownload.vue'
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'

import openLink from '../../services/openLink.js'

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
			inputObject: '',
			mapping: '', // for editing the current mapping / making a new one
			mappings: [],
			mappingsLoading: false,
			isOpenRegisterInstalled: true, // defines if the openregister app is installed, defaults to true
			noOpenRegisterToInstall: false, // defines if the openregister app is NOT available on the server, defaults to false
			schemas: [],
			schemasLoading: false,
			editMapping: false,
			result: {}, // result from the testMapping function
			success: null,
			loading: false,
			error: false,
		}
	},
	computed: {
		/**
		 * defined wether the mapping is a custom mapping or not.
		 * "No mapping" is an option within the mappings dropdown.
		 * when selected you will need to fully provide your own mapping.
		 */
		customMapping() {
			return this.mappings.value.id === 'no-mapping'
		},
	},
	watch: {
		'mappings.value.id'(newVal) {
			if (newVal === 'no-mapping') {
				this.mapping = ''
			} else {
				this.mapping = JSON.stringify(this.mappings.value.fullMapping.mapping, null, 2)
			}
		},
	},
	mounted() {
		this.fetchMappings()
		this.fetchSchemas()
	},
	methods: {
		fetchMappings() {
			this.mappingsLoading = true

			mappingStore.refreshMappingList()
				.then(() => {
					const selectedMapping = mappingStore.mappingList.find((mapping) => mapping.id === (mappingStore.mappingItem?.id || Symbol('mapping item id not found')))

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
		async fetchSchemas() {
			this.schemasLoading = true

			// checking if OpenRegister is installed
			console.info('Fetching schemas from Open Register')
			const response = await fetch('/index.php/apps/openregister/api/schemas', {
				headers: {
					accept: '*/*',
					'accept-language': 'en-US,en;q=0.9,nl;q=0.8',
					'cache-control': 'no-cache',
					pragma: 'no-cache',
					'x-requested-with': 'XMLHttpRequest',
				},
				referrerPolicy: 'no-referrer',
				body: null,
				method: 'GET',
				mode: 'cors',
				credentials: 'include',
			})

			if (!response.ok) {
				console.info('Open Register is not installed')
				this.isOpenRegisterInstalled = false
				this.schemasLoading = false
				return
			}

			const responseData = (await response.json()).results

			this.schemas = {
				options: responseData.map((schema) => ({
					id: schema.id,
					label: schema.name,
					summary: schema.description,
					fullSchema: schema,
				})),
				value: null,
			}

			this.schemasLoading = false
		},
		async installOpenRegister() {
			console.info('Installing Open Register')
			const token = document.querySelector('head[data-requesttoken]').getAttribute('data-requesttoken')

			const response = await fetch('/index.php/settings/apps/enable', {
				headers: {
					accept: '*/*',
					'accept-language': 'en-US,en;q=0.9,nl;q=0.8',
					'cache-control': 'no-cache',
					'content-type': 'application/json',
					pragma: 'no-cache',
					requesttoken: token,
					'x-requested-with': 'XMLHttpRequest, XMLHttpRequest',
				},
				referrerPolicy: 'no-referrer',
				body: '{"appIds":["openregister"],"groups":[]}',
				method: 'POST',
				mode: 'cors',
				credentials: 'include',
			})

			if (!response.ok) {
				console.info('Failed to install Open Register')
				this.noOpenRegisterToInstall = true
			} else {
				console.info('Open Register installed')
				this.isOpenRegisterInstalled = true
				this.fetchSchemas()
			}
		},
		closeModal() {
			navigationStore.setModal(false)
		},
		async testMapping() {
			this.loading = true
			this.success = null
			this.result = {}

			console.log({
				mapping: this.editMapping
					? {
						...this.mappings.value.fullMapping,
						mapping: JSON.parse(this.mapping),
					}
					: this.mappings.value.fullMapping,
				inputObject: this.inputObject,
				schema: this.schemas.value?.fullSchema,
			})

			mappingStore.testMapping({
				mapping: this.editMapping
					? { // apply the edited mapping to the full mapping
						...this.mappings.value.fullMapping,
						mapping: JSON.parse(this.mapping),
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

textarea.textarea__input {
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

.edit-mapping-textarea textarea {
    height: 150px !important;
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
