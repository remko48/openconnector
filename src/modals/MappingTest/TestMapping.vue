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

			<div class="content">
				<TestMappingInputObject
					:input-object="inputObject.value"
					@input-object-changed="receiveInputObject" />
				<TestMappingMappingSelect
					:input-object="inputObject"
					:mapping-item="mapping.selected"
					@mapping-selected="receiveMappingSelected"
					@mapping-test="receiveMappingTest" />
				<TestMappingResult
					:mapping-test="mappingTest" />
			</div>
		</div>
	</NcModal>
</template>

<script>
import {
	NcModal,
} from '@nextcloud/vue'
import TestMappingInputObject from './components/TestMappingInputObject.vue'
import TestMappingMappingSelect from './components/TestMappingMappingSelect.vue'
import TestMappingResult from './components/TestMappingResult.vue'

export default {
	name: 'TestMapping',
	components: {
		NcModal,
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
		}
	},
	methods: {
		receiveInputObject(value) {
			this.inputObject = value
		},
		receiveMappingSelected(value) {
			value.selected && (this.mapping.selected = value.selected)
			value.mappings && (this.mapping.mappings = value.mappings)
			value.loading && (this.mapping.loading = value.loading)
		},
		receiveMappingTest(value) {
			value.result && (this.mappingTest.result = value.result)
			value.success && (this.mappingTest.success = value.success)
			value.loading && (this.mappingTest.loading = value.loading)
			value.error && (this.mappingTest.error = value.error)
		},
		closeModal() {
			navigationStore.setModal(false)
		},
	},
}
</script>

<style>
/* modal */
div[class='modal-container']:has(.TestMappingMainModal) {
    width: clamp(800px, 100%, 1200px) !important;
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
.content > *:first-child {
    width: calc(100% / 4);
}
.content > *:nth-child(2) {
    width: calc(100% / 2);
}
.content > *:last-child {
    width: calc(100% / 4);
}
</style>
