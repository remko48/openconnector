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

				<NcTextArea :value.sync="ruleItem.actionConfig"
					label="Action Configuration (JSON)" />
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
					javascript: ''
				}
			},

			actionOptions: {
				options: [
					{ label: 'Create', id: 'create' },
					{ label: 'Read', id: 'read' },
					{ label: 'Update', id: 'update' },
					{ label: 'Delete', id: 'delete' }
				],
				value: { label: 'Create', id: 'create' }
			},

			typeOptions: {
				options: [
					{ label: 'Error', id: 'error' },
					{ label: 'Mapping', id: 'mapping' },
					{ label: 'Synchronization', id: 'synchronization' },
					{ label: 'JavaScript', id: 'javascript' }
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
				const { results: mappings } = await mappingStore.refreshMappingList()
				
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
			} catch (error) {
				console.error('Failed to fetch mappings:', error)
			} finally {
				this.mappingOptions.loading = false
			}
		},

		async getSynchronizations() {
			try {
				this.syncOptions.loading = true
				const { results: synchronizations } = await synchronizationStore.refreshSynchronizationList()
				
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

			ruleStore.saveRule({
				...this.ruleItem,
				conditions: this.ruleItem.conditions ? JSON.parse(this.ruleItem.conditions) : [],
				action: this.actionOptions.value?.id || null,
				type: this.typeOptions.value?.id || null,
				actionConfig: this.ruleItem.actionConfig ? JSON.parse(this.ruleItem.actionConfig) : {}
			})
				.then(({ response }) => {
					this.success = response.ok
					this.closeTimeoutFunc = setTimeout(this.closeModal, 2000)
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
</style>
