<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="testMapping"
		@close="closeModal">
		<!-- Do not remove this seemingly useless class "TestMappingMainModal" -->
		<div class="modalContent TestMappingMainModal">
			<h2>Mapping test</h2>

			<div v-if="!openRegister.isInstalled" class="openregister-notecard">
				<NcNoteCard
					:type="openRegister.isAvailable ? 'info' : 'error'"
					:heading="openRegister.isAvailable ? 'Open Register is not installed' : 'Failed to install Open Register'">
					<p>
						{{ openRegister.isAvailable
							? 'Some features require Open Register to be installed'
							: 'This either means that Open Register is not available on this server or you need to confirm your password' }}
					</p>

					<div class="install-buttons">
						<NcButton v-if="openRegister.isAvailable"
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
			</div>

			<div class="content">
				<TestMappingInputObject ref="inputObjectRef"
					@input-object-changed="receiveInputObject" />
				<TestMappingMappingSelect ref="mappingSelectRef"
					:input-object="inputObject"
					:open-register="openRegister"
					@open-register="receiveOpenRegister"
					@schema-selected="receiveSchemaSelected"
					@mapping-selected="receiveMappingSelected"
					@mapping-test="receiveMappingTest" />
				<TestMappingResult ref="mappingResultRef"
					:mapping-test="mappingTest" />
			</div>
		</div>
	</NcModal>
</template>

<script>
import {
	NcModal,
	NcNoteCard,
	NcButton,
} from '@nextcloud/vue'

import CloudDownload from 'vue-material-design-icons/CloudDownload.vue'
import OpenInNew from 'vue-material-design-icons/OpenInNew.vue'

import TestMappingInputObject from './components/TestMappingInputObject.vue'
import TestMappingMappingSelect from './components/TestMappingMappingSelect.vue'
import TestMappingResult from './components/TestMappingResult.vue'

export default {
	name: 'TestMapping',
	components: {
		NcModal,
		NcNoteCard,
		NcButton,
	},
	data() {
		return {
			// default data
			inputObject: {
				value: '',
				isValid: false,
			},
			mapping: {
				selected: {}, // selected mapping object
				mappings: [], // all mappings
				loading: false, // loading state of the mappings
			},
			mappingTest: {
				result: {}, // result from the testMapping function
				success: null,
				loading: false,
				error: false,
			},
			openRegister: {
				isInstalled: true,
				isAvailable: true,
			},
			schema: {
				selected: {},
				schemas: [],
				success: null,
				loading: false,
				error: false,
			},
		}
	},
	methods: {
		receiveInputObject(value) {
			this.inputObject = value
		},
		receiveMappingSelected(value) {
			value.selected && (this.mapping.selected = value.selected)
			value.mappings && (this.mapping.mappings = value.mappings)
			value.loading !== undefined && (this.mapping.loading = value.loading) // boolean
		},
		receiveMappingTest(value) {
			value.result && (this.mappingTest.result = value.result)
			value.success !== undefined && (this.mappingTest.success = value.success) // boolean
			value.loading !== undefined && (this.mappingTest.loading = value.loading) // boolean
			value.error !== undefined && (this.mappingTest.error = value.error) // boolean / string
		},
		receiveSchemaSelected(value) {
			value.selected && (this.schema.selected = value.selected)
			value.schemas && (this.schema.schemas = value.schemas)
			value.success !== undefined && (this.schema.success = value.success) // boolean
			value.loading !== undefined && (this.schema.loading = value.loading) // boolean
			value.error !== undefined && (this.schema.error = value.error) // boolean / string
		},
		receiveOpenRegister(value) {
			value.isInstalled !== undefined && (this.openRegister.isInstalled = value.isInstalled) // boolean
			value.isAvailable !== undefined && (this.openRegister.isAvailable = value.isAvailable) // boolean
		},
		closeModal() {
			navigationStore.setModal(false)
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
				this.openRegister.isAvailable = false
			} else {
				console.info('Open Register installed')
				this.openRegister.isInstalled = true
				this.$refs.mappingSelectRef.fetchSchemas()
			}
		},
	},
}
</script>

<style>
/* modal */
div[class='modal-container']:has(.TestMappingMainModal) {
    width: clamp(1000px, 100%, 1200px) !important;
}
</style>

<style scoped>
.content {
    height: 600px;
    display: flex;
    flex-direction: row;
}
.content > * {
    height: 100%;
    overflow: auto;
}
.content > *:not(:last-child) {
    border-right: 1px solid gray;
}
.content > *:first-child {
    width: 25%;
    padding-right: 0.5rem;
}
.content > *:nth-child(2) {
    width: 50%;
    padding: 0 0.5rem;
}
.content > *:last-child {
    width: 25%;
    padding-left: 0.5rem;
}

.content > :deep(h4) {
    margin-top: 0;
}

/* Open Register note card */
.openregister-notecard {
    margin-bottom: 1rem;
}
.openregister-notecard > .notecard {
    width: fit-content;
}
.install-buttons {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}
</style>
