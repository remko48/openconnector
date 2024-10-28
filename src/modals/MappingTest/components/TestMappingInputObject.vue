<script setup>
// import { navigationStore } from '../../store/store.js'
</script>

<template>
	<div>
		<h4>Input object</h4>

		<NcTextArea v-model="localInputObject"
			class="textarea"
			@input="$emit('input-object-changed', $event.target.value)" />
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
	created() {
		this.localInputObject = this.inputObject
	},
}
</script>

<style scoped>
.textarea :deep(textarea) {
    resize: vertical !important;
    height: 100%;
}
</style>
