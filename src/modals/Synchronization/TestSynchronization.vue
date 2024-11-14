<script setup>
import { synchronizationStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'testSynchronization'"
		ref="modalRef"
		label-id="testSynchronization"
		@close="closeModal">
		<div class="modalContent">
			<h2>Test synchronization</h2>

			<div v-if="loading">
				<NcLoadingIcon :size="64" name="Running synchronization test" />
			</div>

			<NcNoteCard v-if="response?.ok" type="success">
				<p>The connection to the synchronization was successful.</p>
			</NcNoteCard>
			<NcNoteCard v-if="!response?.ok || error" type="error">
				<p>
					An error occurred while testing the connection: {{
						synchronizationStore.synchronizationTest
							? synchronizationStore.synchronizationTest.message
								? synchronizationStore.synchronizationTest.message
								: synchronizationStore.synchronizationTest.error
							: response?.statusMessage
								? response?.statusMessage
								: `${response?.status} - ${response?.statusText}`
					}}
				</p>
			</NcNoteCard>

			<div v-if="response">
				<p><b>Status:</b> {{ response?.statusText }} ({{ response?.status }})</p>
				<p><b>Response time:</b> {{ response?.responseTime ?? 'Onbekend' }} (Milliseconds)</p>
				<p><b>Size:</b> {{ response?.size ?? 'Onbekend' }} (Bytes)</p>
				<p><b>Remote IP:</b> {{ response?.remoteIp }}</p>
				<p><b>Headers:</b> {{ response?.headers }}</p>
				<p><b>Body:</b> {{ response?.body }}</p>
			</div>
		</div>
	</NcModal>
</template>

<script>
import {
	NcModal,
	NcLoadingIcon,
	NcNoteCard,
} from '@nextcloud/vue'

export default {
	name: 'TestSynchronization',
	components: {
		NcModal,
		NcLoadingIcon,
		NcNoteCard,
	},
	data() {
		return {
			success: null,
			loading: false,
			error: false,
			updated: false,
			response: null,
		}
	},
	updated() {
		if (navigationStore.modal === 'testSynchronization' && !this.updated) {
			this.testSynchronization()
			this.updated = true
		}
	},
	methods: {
		closeModal() {
			navigationStore.setModal(false)
			synchronizationStore.synchronizationTest = null
			this.success = null
			this.loading = false
			this.error = false
			this.updated = false
		},
		async testSynchronization() {
			this.loading = true

			synchronizationStore.testSynchronization()
				.then(({ response }) => {

					this.response = response

					this.success = response.ok
					this.error = false
					response.ok && this.closeModal()
				}).catch((error) => {
					this.success = false
					this.error = error.message || 'An error occurred while testing the synchronization'
				}).finally(() => {
					this.loading = false
				})
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
