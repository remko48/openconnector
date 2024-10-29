<script setup>
import { mappingStore } from '../../../store/store.js'
</script>

<template>
	<div>
		<h4>Test mapping</h4>

		<div class="content">
			<div class="mapping-select">
				<NcSelect v-bind="mappings"
					v-model="mappings.value"
					input-label="Mapping"
					:clearable="false"
					:loading="mappingsLoading || mappingTest.loading"
					required
					@input="emitMappingSelected">
					<!-- eslint-disable-next-line vue/no-unused-vars vue/no-template-shadow  -->
					<template #no-options="{ search, searching, loading }">
						<p v-if="loading">
							Loading...
						</p>
						<p v-if="!loading && !mappings.options?.length">
							Er zijn geen mappings beschikbaar
						</p>
					</template>
					<!-- eslint-disable-next-line vue/no-unused-vars  -->
					<template #option="{ id, label, summary, removeStyle }">
						<div :class="removeStyle !== true && 'mapping-option'">
							<!-- custom style is enabled -->
							<SitemapOutline v-if="!removeStyle" :size="25" />
							<span v-if="!removeStyle">
								<h6 style="margin: 0">
									{{ label }}
								</h6>
								{{ summary }}
							</span>
							<!-- custom style is disabled -->
							<p v-if="removeStyle">
								{{ label }}
							</p>
						</div>
					</template>
				</NcSelect>

				<NcButton :disabled="mappingTest.loading || !mappings.value || !inputObject.isValid"
					type="success"
					@click="testMapping()">
					<template #icon>
						<NcLoadingIcon v-if="mappingTest.loading" :size="20" />
						<ContentSaveOutline v-if="!mappingTest.loading" :size="20" />
					</template>
					Test
				</NcButton>
			</div>
		</div>
	</div>
</template>

<script>
import {
	NcSelect,
	NcButton,
	NcLoadingIcon,
} from '@nextcloud/vue'

import SitemapOutline from 'vue-material-design-icons/SitemapOutline.vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
	name: 'TestMappingMappingSelect',
	components: {
		NcSelect,
		NcButton,
		NcLoadingIcon,
	},
	props: {
		inputObject: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			mappings: [],
			mappingsLoading: false,
			// mapping test
			mappingTest: {
				result: {}, // result from the testMapping function
				success: null,
				loading: false,
				error: false,
			},
		}
	},
	watch: {
		mappingTest: {
			handler(newVal) {
				this.$emit('mapping-test', {
					...newVal,
				})
			},
			deep: true,
		},
		mappingsLoading(newVal) {
			this.$emit('mapping-selected', {
				loading: newVal,
			})
		},
	},
	mounted() {
		this.fetchMappings()
	},
	methods: {
		emitMappingSelected(event) {
			this.$emit('mapping-selected', {
				selected: event,
			})
		},
		fetchMappings(currentMappingItem = null) {
			this.mappingsLoading = true

			mappingStore.refreshMappingList()
				.then(() => {
					if (!currentMappingItem) {
						currentMappingItem = mappingStore.mappingItem || null
					}

					const selectedMapping = mappingStore.mappingList.find((mapping) => mapping.id === (currentMappingItem?.id || Symbol('mapping item id not found')))

					const fallbackMapping = mappingStore.mappingList[0]
						? {
							id: mappingStore.mappingList[0].id,
							label: mappingStore.mappingList[0].name,
							summary: mappingStore.mappingList[0].description,
							fullMapping: mappingStore.mappingList[0],
						}
						: null

					this.mappings = {
						options: [
							...mappingStore.mappingList.map((mapping) => ({
								id: mapping.id,
								label: mapping.name,
								summary: mapping.description,
								fullMapping: mapping,
							})),
						],
						value: selectedMapping
							? {
								id: selectedMapping.id,
								label: selectedMapping.name,
								summary: selectedMapping.description,
								fullMapping: selectedMapping,
							}
							: fallbackMapping,
					}

					// emit the current selected mapping after mappings initialization
					this.$emit('mapping-selected', {
						mappings: this.mappings,
						selected: this.mappings.value,
					})
				})
				.finally(() => {
					this.mappingsLoading = false
				})
		},
		async testMapping() {
			this.loading = true

			mappingStore.testMapping({
				mapping: this.mappings.value.fullMapping,
				inputObject: JSON.parse(this.inputObject.value),
			})
				.then(({ response, data }) => {
					this.mappingTest.success = response.ok
					this.mappingTest.result = data
				})
				.catch((error) => {
					this.mappingTest.error = error.message || 'An error occurred while testing the mapping'
				})
				.finally(() => {
					this.mappingTest.loading = false
				})
		},
	},
}
</script>

<style scoped>
.content {
    text-align: left;
}

.textarea :deep(textarea) {
    resize: vertical !important;
    height: 100%;
}

.mapping-select {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 10px;
}
.mapping-select > .button-vue {
    margin-bottom: 4px !important;
}

/* Mapping option */
.mapping-option {
    display: flex;
    align-items: center;
    gap: 10px;
}
.mapping-option > .material-design-icon {
    margin-top: 2px;
}
.mapping-option > h6 {
    line-height: 0.8;
}

/* select style */
/* remove box-shadow around search input */
.v-select :deep(.vs__search) {
    box-shadow: none !important;
}
</style>
