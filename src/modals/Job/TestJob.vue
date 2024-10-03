<script setup>
import { jobStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'testJob'"
		ref="modalRef"
		label-id="testJob"
		@close="closeModal">
		<div class="modalContent">
			<h2>Test job</h2>

			<form @submit.prevent="handleSubmit">
				<div class="form-group">
					<div class="testJobDetailGrid">
						<NcTextField
							id="jobId"
							label="Job ID"
							:value.sync="testJobItem.jobId" />
					</div>
				</div>
			</form>

			<NcButton
				:disabled="loading"
				type="primary"
				@click="testJob()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<Sync v-if="!loading" :size="20" />
				</template>
				Test job
			</NcButton>

			<NcNoteCard v-if="jobStore.jobTest && jobStore.jobTest.success" type="success">
				<p>The job test was successful.</p>
			</NcNoteCard>
			<NcNoteCard v-if="(jobStore.jobTest && !jobStore.jobTest.success) || error" type="error">
				<p>An error occurred while testing the job: {{ jobStore.jobTest ? jobStore.jobTest.message : error }}</p>
			</NcNoteCard>

			<div v-if="jobStore.jobTest">
				<p><b>Status:</b> {{ jobStore.jobTest.status }}</p>
				<p><b>Execution time:</b> {{ jobStore.jobTest.executionTime }} (Milliseconds)</p>
				<p><b>Result:</b> {{ jobStore.jobTest.result }}</p>
			</div>
		</div>
	</NcModal>
</template>

<script>
import {
	NcButton,
	NcModal,
	NcLoadingIcon,
	NcTextField,
	NcNoteCard,
} from '@nextcloud/vue'
import Sync from 'vue-material-design-icons/Sync.vue'

export default {
	name: 'TestJob',
	components: {
		NcModal,
		NcButton,
		NcLoadingIcon,
		NcTextField,
		NcNoteCard,
	},
	data() {
		return {
			testJobItem: {
				jobId: '',
			},
			success: false,
			loading: false,
			error: false,
		}
	},
	methods: {
		closeModal() {
			navigationStore.setModal(false)
			this.success = false
			this.loading = false
			this.error = false
			this.testJobItem = {
				jobId: '',
			}
		},
		async testJob() {
			this.loading = true

			try {
				await jobStore.testJob(this.testJobItem.jobId)
				this.success = true
				this.loading = false
				this.error = false
			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'An error occurred while testing the job'
				jobStore.setJobTest(false)
			}
		},
	},
}
</script>
<style>
.testJobDetailGrid {
	display: grid;
	grid-template-columns: 1fr;
	gap: 5px;
}
</style>
