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
							<template #option="{ id, label, summary }">
								<div class="mapping-option">
									<SitemapOutline :size="25" />
									<span>
										<h6 style="margin: 0">
											{{ label }}
										</h6>
										{{ summary }}
									</span>
								</div>
							</template>
						</NcSelect>

						<!-- <NcButton
							aria-label="Edit mapping"
							size="normal"
							type="primary"
							@click="editMapping = true">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Edit mapping
						</NcButton> -->
					</div>

					<NcTextArea
						label="Input object"
						:value.sync="inputObject"
						:error="!validInputObjectJson"
						:error-message="!validInputObjectJson ? 'Invalid JSON' : ''" />

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
				<NcButton :disabled="loading || !mappings.value || !inputObject || !validInputObjectJson"
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
		validInputObjectJson() {
			try {
				JSON.parse(this.inputObject)
				return true
			} catch (e) {
				return false
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
						options: mappingStore.mappingList.map((mapping) => ({
							id: mapping.id,
							label: mapping.name,
							summary: mapping.description,
							fullMapping: mapping,
						})),
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

			mappingStore.testMapping({
				mapping: this.mappings.value.fullMapping,
				inputObject: this.inputObject,
				schema: this.schemas.value?.fullSchema,
			}).then(({ response, data }) => {
				this.success = response.ok
				this.result = data
			}).catch((error) => {
				this.error = error.message || 'An error occurred while testing the mapping'
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

.flex-container {
    display: flex;
    align-items: center;
    gap: 10px;
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
