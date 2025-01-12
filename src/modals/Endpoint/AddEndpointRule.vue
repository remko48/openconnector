<script setup>
import { endpointStore, navigationStore, ruleStore } from '../../store/store.js'
</script>

<template>
    <NcModal v-if="navigationStore.modal === 'addEndpointRule'"
        ref="modalRef"
        label-id="addEndpointRule"
        @close="closeModal">
        <div class="modalContent">
            <h2>Add Rule to Endpoint</h2>

            <div v-if="success || error">
                <NcNoteCard v-if="success" type="success">
                    <p>Rule successfully added to endpoint</p>
                </NcNoteCard>
                <NcNoteCard v-if="error" type="error">
                    <p>{{ error }}</p>
                </NcNoteCard>
            </div>

            <form v-if="!success" @submit.prevent="handleSubmit">
                <NcSelect
                    v-bind="ruleOptions"
                    v-model="ruleOptions.value"
                    :loading="loading"
                    input-label="Select Rule"
                    :multiple="false"
                    :clearable="false" />
            </form>

            <NcButton v-if="!success"
                :disabled="loading || !ruleOptions.value"
                type="primary"
                @click="addRule">
                <template #icon>
                    <NcLoadingIcon v-if="loading" :size="20" />
                    <ContentSaveOutline v-if="!loading" :size="20" />
                </template>
                Save
            </NcButton>
        </div>
    </NcModal>
</template>

<script>
import {
    NcButton,
    NcModal,
    NcSelect,
    NcLoadingIcon,
    NcNoteCard,
} from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
    name: 'AddEndpointRule',
    components: {
        NcModal,
        NcButton,
        NcSelect,
        NcLoadingIcon,
        NcNoteCard,
    },
    data() {
        return {
            success: null,
            loading: false,
            error: false,
            ruleOptions: {
                options: [],
                value: null
            },
            closeTimeoutFunc: null
        }
    },
    async mounted() {
        await this.loadAvailableRules()
    },
    methods: {
        async loadAvailableRules() {
            try {
                this.loading = true
                await ruleStore.refreshRuleList()
                
                // Filter out rules that are already added to the endpoint
                const availableRules = ruleStore.ruleList.filter(rule => 
                    !endpointStore.endpointItem.rules?.includes(rule.id)
                )

                this.ruleOptions.options = availableRules.map(rule => ({
                    label: rule.name,
                    value: rule.id
                }))
            } catch (error) {
                console.error('Failed to load rules:', error)
                this.error = 'Failed to load available rules'
            } finally {
                this.loading = false
            }
        },

        async addRule() {
            if (!this.ruleOptions.value) return

            this.loading = true
            try {
                // Create a copy of the current endpoint
                const updatedEndpoint = { ...endpointStore.endpointItem }
                
                // Initialize rules array if it doesn't exist
                if (!updatedEndpoint.rules) {
                    updatedEndpoint.rules = []
                } else if (!Array.isArray(updatedEndpoint.rules)) {
                    // If rules exists but is not an array, convert it to array
                    updatedEndpoint.rules = []
                }

                // Add the new rule ID
                updatedEndpoint.rules.push(this.ruleOptions.value.value)

                // Ensure we're not modifying properties that should remain unchanged
                const endpointToSave = {
                    ...updatedEndpoint,
                    endpointArray: Array.isArray(updatedEndpoint.endpointArray) 
                        ? updatedEndpoint.endpointArray 
                        : updatedEndpoint.endpointArray.split(/ *, */g),
                    rules: updatedEndpoint.rules
                }

                const { response } = await endpointStore.saveEndpoint(endpointToSave)
                
                if (response.ok) {
                    this.success = true
                    this.error = false
                    this.closeTimeoutFunc = setTimeout(this.closeModal, 2000)
                } else {
                    this.success = false
                    this.error = 'Failed to add rule to endpoint'
                }
            } catch (error) {
                console.error('Error adding rule:', error)
                this.success = false
                this.error = error.message || 'An error occurred while adding the rule'
            } finally {
                this.loading = false
            }
        },

        closeModal() {
            navigationStore.setModal(false)
            clearTimeout(this.closeTimeoutFunc)
            this.success = null
            this.loading = false
            this.error = false
            this.ruleOptions.value = null
        }
    }
}
</script> 