<template>
  <div>
    <DeleteJob v-if="navigationStore.modal === 'deleteJob'" />
    <EditJob v-if="navigationStore.modal === 'editJob'" />
    <DeleteLog v-if="navigationStore.modal === 'deleteLog'" />
    <EditLog v-if="navigationStore.modal === 'editLog'" />
    <DeleteMapping v-if="navigationStore.modal === 'deleteMapping'" />
    <EditMapping v-if="navigationStore.modal === 'editMapping'" />
    <DeleteSynchronization v-if="navigationStore.modal === 'deleteSynchronization'" />
    <EditSynchronization v-if="navigationStore.modal === 'editSynchronization'" />
    <!-- Existing modals -->
    <DeleteCondition v-if="navigationStore.modal === 'deleteCondition'" />
    <EditCondition v-if="navigationStore.modal === 'editCondition'" />
  </div>
</template>

<script>
import { navigationStore } from '../store/store.js'
import DeleteJob from './modals/Job/DeleteJob.vue'
import EditJob from './modals/Job/EditJob.vue'
import DeleteLog from './modals/Log/DeleteLog.vue'
import EditLog from './modals/Log/EditLog.vue'
import DeleteMapping from './modals/Mapping/DeleteMapping.vue'
import EditMapping from './modals/Mapping/EditMapping.vue'
import DeleteSynchronization from './modals/Synchronization/DeleteSynchronization.vue'
import EditSynchronization from './modals/Synchronization/EditSynchronization.vue'
import DeleteCondition from './modals/DeleteCondition.vue'
import EditCondition from './modals/EditCondition.vue'

export default {
  name: 'Modals',
  components: {
    DeleteJob,
    EditJob,
    DeleteLog,
    EditLog,
    DeleteMapping,
    EditMapping,
    DeleteSynchronization,
    EditSynchronization,
    DeleteCondition,
    EditCondition,
  },
  setup() {
    return {
      navigationStore,
    }
  },
}
</script>