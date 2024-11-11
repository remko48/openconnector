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
					-->{{ JSON.stringify(mappingTest.result?.resultObject, null, 2) }}
				</pre>
			</div>

			<div v-if="mappingTest.result?.isValid !== undefined">
				<p v-if="mappingTest.result.isValid" class="valid">
					<NcIconSvgWrapper inline :path="mdiCheckCircle" /> input object is valid
				</p>
				<p v-if="!mappingTest.result.isValid" class="invalid">
					<NcIconSvgWrapper inline :path="mdiCloseCircle" /> input object is invalid
				</p>
			</div>

			<div v-if="mappingTest.result?.validationErrors" class="validation-errors">
				<table>
					<thead>
						<tr>
							<th>Field</th>
							<th>Errors</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(errors, field) in mappingTest.result.validationErrors" :key="field">
							<td>{{ field }}</td>
							<td>
								<ul>
									<li v-for="error in errors" :key="error">
										{{ error }}
									</li>
								</ul>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</template>

<script>
import {
	NcNoteCard,
	NcIconSvgWrapper,
} from '@nextcloud/vue'

import { mdiCheckCircle, mdiCloseCircle } from '@mdi/js'

export default {
	name: 'TestMappingResult',
	components: {
		NcNoteCard,
		NcIconSvgWrapper,
	},
	props: {
		mappingTest: {
			type: Object,
			required: true,
		},
	},
	setup() {
		return {
			mdiCheckCircle,
			mdiCloseCircle,
		}
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

.valid {
	color: var(--color-success);
}
.invalid {
	color: var(--color-error);
}
.validation-errors {
    margin-block-start: 0.5rem;
    overflow-x: auto;
    width: 100%;
}
.validation-errors table {
    border: 1px solid grey;
    border-collapse: collapse;
}
.validation-errors th, .validation-errors td {
    border: 1px solid grey;
    padding: 8px;
}
</style>
