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

			<div class="result">
				<pre><!-- do NOT remove this comment
					-->{{ JSON.stringify(mappingTest.result, null, 2) }}
				</pre>
			</div>
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
    color: var(--color-main-text);
    background-color: var(--color-main-background);
    min-width: 0;
    border-radius: var(--border-radius-large);
    box-shadow: 0 0 10px var(--color-box-shadow);
    height: fit-content;
    padding: 15px;
    margin: 20px 0.5rem;
}
.result pre {
	white-space: break-spaces;
}
</style>
