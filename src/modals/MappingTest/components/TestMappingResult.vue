<template>
	<div>
		<h4>Test result</h4>

		<div class="content">
			<NcNoteCard v-if="mappingTest.success" type="success">
				<p>Mapping successfully tested</p>
			</NcNoteCard>
			<NcNoteCard v-if="mappingTest.success === false" type="error">
				<p>Mapping failed to test</p>
			</NcNoteCard>
			<NcNoteCard v-if="mappingTest.error" type="error">
				<p>{{ mappingTest.error }}</p>
			</NcNoteCard>

			<NcGuestContent class="result">
				<pre><!-- do NOT remove this comment
					-->{{ JSON.stringify(mappingTest.result, null, 2) }}
				</pre>
			</NcGuestContent>
		</div>
	</div>
</template>

<script>
import {
	NcNoteCard,
	NcGuestContent,
} from '@nextcloud/vue'

export default {
	name: 'TestMappingResult',
	components: {
		NcNoteCard,
		NcGuestContent,
	},
	props: {
		mappingTest: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			// data here
		}
	},
}
</script>

<style scoped>
.content {
    text-align: left;
}
.result {
	margin-inline: 0.5rem !important;
}
</style>
