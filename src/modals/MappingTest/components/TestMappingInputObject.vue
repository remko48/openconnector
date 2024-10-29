<script setup>
// import { navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="input-object-container">
		<h4>Input object</h4>

		<NcTextArea v-model="localInputObject"
			class="textarea"
			:error="!validJson(localInputObject)"
			:helper-text="!validJson(localInputObject) ? 'Invalid JSON' : ''"
			@input="emitInputObjectChanged($event)" />
	</div>
</template>

<script>
import {
	NcTextArea,
} from '@nextcloud/vue'

export default {
	name: 'TestMappingInputObject',
	components: {
		NcTextArea,
	},
	props: {
		inputObject: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			localInputObject: '',
		}
	},
	watch: {
		inputObject(newVal) {
			this.localInputObject = newVal
		},
	},
	methods: {
		emitInputObjectChanged(event) {
			const data = {
				value: event.target.value,
				isValid: this.validJson(event.target.value),
			}
			this.$emit('input-object-changed', data)
		},
		validJson(object) {
			try {
				JSON.parse(object)
				return true
			} catch (e) {
				return false
			}
		},
	},
}
</script>

<style scoped>
.input-object-container {
    display: flex;
    flex-direction: column;
}
.input-object-container .textarea {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.input-object-container .textarea :deep(.textarea__main-wrapper) {
    flex-grow: 0.986; /* why this value? idk, it just works */
}

.textarea :deep(textarea) {
    resize: vertical !important;
    height: 100%;
}
</style>
