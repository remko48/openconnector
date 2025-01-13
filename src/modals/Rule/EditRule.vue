<script setup>
import { ruleStore, navigationStore, mappingStore, synchronizationStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="editRule"
		@close="closeModal">
		<div class="modalContent">
			<h2>{{ ruleItem.id ? 'Edit' : 'Add' }} Rule</h2>

			<!-- ====================== -->
			<!-- Success/Error notecard -->
			<!-- ====================== -->
			<div v-if="success || error">
				<NcNoteCard v-if="success" type="success">
					<p>Rule successfully saved</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error || 'An error occurred' }}</p>
				</NcNoteCard>
			</div>

			<!-- ====================== -->
			<!--          Form          -->
			<!-- ====================== -->
			<form v-if="!success" @submit.prevent="handleSubmit">
				<NcTextField :value.sync="ruleItem.name"
					label="Name"
					required />

				<NcTextArea :value.sync="ruleItem.description"
					label="Description" />

				<div class="json-editor">
					<label>Conditions (JSON Logic)</label>
					<textarea
						v-model="ruleItem.conditions"
						:class="{ 'invalid-json': !isValidJson(ruleItem.conditions) }"
						@input="validateJson"
						rows="5"
						placeholder='{"and": [{"==": [{"var": "status"}, "active"]}, {">=": [{"var": "age"}, 18]}]}'
					></textarea>
					<span v-if="!isValidJson(ruleItem.conditions)" class="error-message">
						Invalid JSON format
					</span>
				</div>

				<NcTextField :value.sync="ruleItem.order"
					label="Order"
					type="number" />

				<NcSelect v-bind="actionOptions"
					v-model="actionOptions.value" 
					input-label="Action" />

				<NcSelect v-bind="typeOptions"
					v-model="typeOptions.value"
					input-label="Type" />

				<!-- Add mapping select -->
				<NcSelect v-if="typeOptions.value?.id === 'mapping'"
					v-bind="mappingOptions"
					v-model="mappingOptions.value"
					:loading="mappingOptions.loading"
					input-label="Select Mapping"
					:multiple="false"
					:clearable="false" />

				<!-- Add synchronization select -->
				<NcSelect v-if="typeOptions.value?.id === 'synchronization'"
					v-bind="syncOptions"
					v-model="syncOptions.value"
					:loading="syncOptions.loading"
					input-label="Select Synchronization"
					:multiple="false"
					:clearable="false" />

				<!-- Error Configuration -->
				<template v-if="typeOptions.value?.id === 'error'">
					<NcInputField
						type="number"
						label="Error Code"
						:min="100"
						:max="999"
						:value.sync="ruleItem.configuration.error.code"
						placeholder="500" />
					
					<NcTextField
						label="Error Title"
						maxlength="255"
						:value.sync="ruleItem.configuration.error.name"
						placeholder="Something went wrong" />
					
					<NcTextArea
						label="Error Message"
						maxlength="2550"
						:value.sync="ruleItem.configuration.error.message"
						placeholder="We encountered an unexpected problem" />
				</template>

				<!-- JavaScript Configuration -->
				<template v-if="typeOptions.value?.id === 'javascript'">
					<NcTextArea
						label="JavaScript Code"
						:value.sync="ruleItem.configuration.javascript"
						class="code-editor"
						placeholder="Enter your JavaScript code here..."
						rows="10" />
				</template>

				<!-- Authentication Configuration -->
				<template v-if="typeOptions.value?.id === 'authentication'">
					<NcSelect
						:options="[
							{ label: 'Basic Authentication', value: 'basic' },
							{ label: 'JWT', value: 'jwt' },
							{ label: 'JWT-ZGW', value: 'jwt-zgw' },
							{ label: 'OAuth', value: 'oauth' }
						]"
						v-model="ruleItem.configuration.authentication.type"
						input-label="Authentication Type" />

					<!-- Users Multi-Select -->
					<NcSelect
						:options="usersList"
						v-model="ruleItem.configuration.authentication.users"
						input-label="Allowed Users"
						:multiple="true"
						:clearable="true"
						placeholder="Select users who can access" />

					<!-- Groups Multi-Select -->
					<NcSelect
						:options="groupsList"
						v-model="ruleItem.configuration.authentication.groups"
						input-label="Allowed Groups"
						:multiple="true"
						:clearable="true"
						placeholder="Select groups who can access" />
				</template>

				<!-- Download Configuration -->
				<template v-if="typeOptions.value?.id === 'download'">
					<NcTextField
						label="File ID Position"
						type="number"
						:min="0"
						:value.sync="ruleItem.configuration.download.fileIdPosition"
						placeholder="Position of file ID in URL path (e.g. 2)" />
					
					<div class="info-text">
						<p>The system will automatically check if the authenticated user has access rights to the requested file.</p>
					</div>
				</template>
			</form>

			<NcButton v-if="!success"
				:disabled="loading || !ruleItem.name || !isValidJson(ruleItem.conditions)"
				type="primary"
				@click="editRule()">
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
	NcInputField,
} from '@nextcloud/vue'

import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
	name: 'EditRule',
	components: {
		NcModal,
		NcButton,
		NcTextField,
		NcTextArea,
		NcSelect,
		NcLoadingIcon,
		NcNoteCard,
		NcInputField,
	},
	data() {
		return {
			IS_EDIT: !!ruleStore.ruleItem?.id,
			success: null,
			loading: false,
			error: false,
			mappingOptions: {
				options: [],
				value: null,
				loading: false
			},
			
			syncOptions: {
				options: [],
				value: null,
				loading: false
			},

			// @todoMock data for users and groups - should be fetched from backend
			usersList: [
				{ label: 'User 1', value: 'user1' },
				{ label: 'User 2', value: 'user2' }
			],
			groupsList: [
				{ label: 'Group 1', value: 'group1' },
				{ label: 'Group 2', value: 'group2' }
			],

			ruleItem: {
				name: '',
				description: '',
				conditions: '',
				order: 0,
				action: '',
				type: '',
				actionConfig: '{}',
				configuration: {
					mapping: null,
					synchronization: null,
					error: {
						code: 500,
						name: 'Something went wrong',
						message: 'We encountered an unexpected problem'
					},
					javascript: '',
					authentication: {
						type: 'basic',
						users: [],
						groups: []
					},
					download: {
						fileIdPosition: 0
					}
				}
			},

			actionOptions: {
				options: [
					{ label: 'Post (Create)', id: 'post' },
					{ label: 'Get (Read)', id: 'get' },
					{ label: 'Put (Update)', id: 'put' },
					{ label: 'Delete (Delete)', id: 'delete' }
				],
				value: { label: 'Create', id: 'create' }
			},

			typeOptions: {
				options: [
					{ label: 'Error', id: 'error' },
					{ label: 'Mapping', id: 'mapping' },
					{ label: 'Synchronization', id: 'synchronization' },
					{ label: 'JavaScript', id: 'javascript' },
					{ label: 'Authentication', id: 'authentication' },
					{ label: 'Download', id: 'download' }
				],
				value: { label: 'Error', id: 'error' }
			},

			closeTimeoutFunc: null
		}
	},
	mounted() {
		if (this.IS_EDIT) {
			this.ruleItem = {
				...ruleStore.ruleItem,
				conditions: JSON.stringify(ruleStore.ruleItem.conditions, null, 2),
				actionConfig: JSON.stringify(ruleStore.ruleItem.actionConfig)
			}

			this.actionOptions.value = this.actionOptions.options.find(
				option => option.id === this.ruleItem.action
			)

			this.typeOptions.value = this.typeOptions.options.find(
				option => option.id === this.ruleItem.type  
			)
		}
		this.getMappings()
		this.getSynchronizations()
	},
	methods: {
		async getMappings() {
			try {
				this.mappingOptions.loading = true
				await mappingStore.refreshMappingList()
				
				// Use the store's mappingList directly
				const mappings = mappingStore.mappingList
				if (mappings?.length) {
					this.mappingOptions.options = mappings.map(mapping => ({
						label: mapping.name,
						value: mapping.id
					}))

					// Set active mapping if editing
					if (this.IS_EDIT && this.ruleItem.configuration?.mapping) {
						const activeMapping = this.mappingOptions.options.find(
							option => option.value === this.ruleItem.configuration.mapping
						)
						if (activeMapping) {
							this.mappingOptions.value = activeMapping
						}
					}
				}
			} catch (error) {
				console.error('Failed to fetch mappings:', error)
			} finally {
				this.mappingOptions.loading = false
			}
		},

		async getSynchronizations() {
			try {
				this.syncOptions.loading = true
				await synchronizationStore.refreshSynchronizationList()
				
				// Use the store's synchronizationList directly
				const synchronizations = synchronizationStore.synchronizationList
				if (synchronizations?.length) {
					this.syncOptions.options = synchronizations.map(sync => ({
						label: sync.name,
						value: sync.id
					}))

					// Set active synchronization if editing
					if (this.IS_EDIT && this.ruleItem.configuration?.synchronization) {
						const activeSync = this.syncOptions.options.find(
							option => option.value === this.ruleItem.configuration.synchronization
						)
						if (activeSync) {
							this.syncOptions.value = activeSync
						}
					}
				}
			} catch (error) {
				console.error('Failed to fetch synchronizations:', error)
			} finally {
				this.syncOptions.loading = false
			}
		},

		closeModal() {
			navigationStore.setModal(false)
			clearTimeout(this.closeTimeoutFunc)
		},

		isValidJson(str) {
			if (!str) return true
			try {
				JSON.parse(str)
				return true
			} catch (e) {
				return false
			}
		},

		validateJson() {
			try {
				if (this.ruleItem.conditions) {
					// Format the JSON with proper indentation
					const parsed = JSON.parse(this.ruleItem.conditions)
					this.ruleItem.conditions = JSON.stringify(parsed, null, 2)
				}
			} catch (e) {
				// Keep invalid JSON as-is to allow user to fix it
			}
		},

		editRule() {
			this.loading = true

			const configuration = {}
			const type = this.typeOptions.value?.id

			// Build configuration based on type
			switch (type) {
				case 'error':
					configuration.error = {
						code: this.ruleItem.configuration.error.code,
						name: this.ruleItem.configuration.error.name,
						message: this.ruleItem.configuration.error.message
					}
					break
				case 'mapping':
					configuration.mapping = this.mappingOptions.value?.value
					break
				case 'synchronization':
					configuration.synchronization = this.syncOptions.value?.value
					break
				case 'javascript':
					configuration.javascript = this.ruleItem.configuration.javascript
					break
				case 'authentication':
					configuration.authentication = {
						type: this.ruleItem.configuration.authentication.type,
						users: this.ruleItem.configuration.authentication.users,
						groups: this.ruleItem.configuration.authentication.groups
					}
					break
				case 'download':
					configuration.download = {
						fileIdPosition: this.ruleItem.configuration.download.fileIdPosition
					}
					break
			}

			ruleStore.saveRule({
				...this.ruleItem,
				conditions: this.ruleItem.conditions ? JSON.parse(this.ruleItem.conditions) : [],
				action: this.actionOptions.value?.id || null,
				type: type || null,
				configuration
			})
				.then(({ response, entity }) => {
					if (response.ok && entity) {
						this.success = true
						this.error = false
						this.closeTimeoutFunc = setTimeout(this.closeModal, 2000)
					} else {
						this.success = false
						this.error = 'Failed to save rule'
					}
				})
				.catch(error => {
					this.success = false
					this.error = error.message || 'An error occurred while saving the rule'
				})
				.finally(() => {
					this.loading = false
				})
		}
	}
}
</script>

<style scoped>
.json-editor {
	margin-bottom: 1rem;
}

.json-editor label {
	display: block;
	margin-bottom: 0.5rem;
	font-weight: bold;
}

.json-editor textarea {
	width: 100%;
	padding: 0.5rem;
	font-family: monospace;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	background-color: var(--color-main-background);
}

.json-editor .invalid-json {
	border-color: var(--color-error);
}

.json-editor .error-message {
	color: var(--color-error);
	font-size: 0.8rem;
	margin-top: 0.25rem;
	display: block;
}

/* Add styles for the code editor */
.code-editor {
	font-family: monospace;
	width: 100%;
	background-color: var(--color-background-dark);
}

.info-text {
	margin: 1rem 0;
	padding: 0.5rem;
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius);
}
</style>
