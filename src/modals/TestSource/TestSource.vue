<script setup>
import { sourceStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'testSource'"
		ref="modalRef"
		label-id="testSource"
		@close="closeModal">
		<div class="modalContent">
			<h2>Test source</h2>

			<form v-if="!success" @submit.prevent="handleSubmit">
				<div class="form-group">
					<div class="detailGrid">
						<NcSelect
							id="method"
							v-bind="methodOptions"
							v-model="methodOptions.value" />

						<NcSelect
							id="type"
							v-bind="typeOptions"
							v-model="typeOptions.value" />

						<NcTextField
							id="endpoint"
							label="Endpoint"
							:value.sync="testSourceItem.endpoint" />
					</div>
					<NcTextArea
						id="body"
						label="Body"
						:value.sync="testSourceItem.body" />
				</div>
			</form>

			<NcButton
				v-if="!success"
				:disabled="loading"
				type="primary"
				@click="testSource()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<Sync v-if="!loading" :size="20" />
				</template>
				Test connection
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
	NcTextField,
	NcTextArea,
} from '@nextcloud/vue'
import Sync from 'vue-material-design-icons/Sync.vue'

export default {
	name: 'TestSource',
	components: {
		NcModal,
		NcButton,
		NcSelect,
		NcLoadingIcon,
		NcTextField,
		NcTextArea,
	},
	data() {
		return {
			testSourceItem: {
				endpoint: '',
				body: '',
				method: '',
				type: '',
			},
			success: false,
			loading: false,
			error: false,
			typeOptions: {
				inputLabel: 'Type',
				options: [
					{ id: 'JSON', label: 'JSON' },
					{ id: 'XML', label: 'XML' },
					{ id: 'YAML', label: 'YAML' },
				],
				value: { id: 'JSON', label: 'JSON' },
			},
			methodOptions: {
				inputLabel: 'Method',
				options: [
					{ id: 'GET', label: 'GET' },
					{ id: 'POST', label: 'POST' },
					{ id: 'PUT', label: 'PUT' },
					{ id: 'DELETE', label: 'DELETE' },
				],
				value: { id: 'POST', label: 'POST' },

			},
		}
	},
	methods: {
		closeModal() {
			navigationStore.setModal(false)
			this.succes = false
			this.loading = false
			this.error = false
			this.testSourceItem = {
				endpoint: '',
				body: '',
				method: '',
				type: '',
			}
		},
		async testSource() {
			this.loading = true

			try {
				await sourceStore.testSource({
					...this.testSourceItem,
					method: this.methodOptions.value.id,
					type: this.typeOptions.value.id,
				})
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
<style>
.detailGrid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 5px;
}
</style>
