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

			<NcNoteCard v-if="synchronizationStore.synchronizationTest && synchronizationStore.synchronizationTest.response.statusCode.toString().startsWith('2')" type="success">
				<p>The connection to the synchronization was successful.</p>
			</NcNoteCard>
			<NcNoteCard v-if="(synchronizationStore.synchronizationTest && !synchronizationStore.synchronizationTest.response.statusCode.toString().startsWith('2')) || error" type="error">
				<p>An error occurred while testing the connection: {{ synchronizationStore.synchronizationTest ? synchronizationStore.synchronizationTest.response.statusMessage : error }}</p>
			</NcNoteCard>

			<div v-if="synchronizationStore.synchronizationTest">
				<p><b>Status:</b> {{ synchronizationStore.synchronizationTest.response.statusMessage }} ({{ synchronizationStore.synchronizationTest.response.statusCode }})</p>
				<p><b>Response time:</b> {{ synchronizationStore.synchronizationTest.response.responseTime }} (Milliseconds)</p>
				<p><b>Size:</b> {{ synchronizationStore.synchronizationTest.response.size }} (Bytes)</p>
				<p><b>Remote IP:</b> {{ synchronizationStore.synchronizationTest.response.remoteIp }}</p>
				<p><b>Headers:</b> {{ synchronizationStore.synchronizationTest.response.headers }}</p>
				<p><b>Body:</b> {{ synchronizationStore.synchronizationTest.response.body }}</p>
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
