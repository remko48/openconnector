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
				<TestMappingInputObject ref="inputObjectRef"
					@input-object-changed="receiveInputObject" />
				<TestMappingMappingSelect ref="mappingSelectRef"
					:input-object="inputObject"
					@schema-selected="receiveSchemaSelected"
					@mapping-selected="receiveMappingSelected"
					@mapping-test="receiveMappingTest" />
				<TestMappingResult ref="mappingResultRef"
					:mapping-test="mappingTest"
					:schema="schema" />
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
			value?.selected !== undefined && (this.schema.selected = value.selected)
			value.schemas && (this.schema.schemas = value.schemas)
			value.success !== undefined && (this.schema.success = value.success) // boolean
			value.loading !== undefined && (this.schema.loading = value.loading) // boolean
			value.error !== undefined && (this.schema.error = value.error) // boolean / string
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
    padding-right: var(--OC-margin-30);
}
.content > *:nth-child(2) {
    width: 50%;
     padding: 0 var(--OC-margin-30);
}
.content > *:last-child {
    width: 25%;
	 padding-left: var(--OC-margin-30);

}

.content > :deep(h4) {
    margin-top: 0;
}

/* Open Register note card */
.openregister-notecard {
   display: flex;
   justify-content: center;
}
.openregister-notecard > .notecard {
    width: fit-content;
    /* max-width: 500px; */
}

</style>
