<template>
	<div>
		<DeleteSource />
		<EditSource />
		<TestSource />
		<DeleteJob />
		<EditJob />
		<DeleteLog />
		<EditLog />
		<DeleteMapping />
		<EditMapping />
		<DeleteSynchronization />
		<EditSynchronization />
	</div>
</template>

<script>
import { navigationStore } from '../store/store.js'
import EditSource from './Source/EditSource.vue'
import TestSource from './TestSource/TestSource.vue'
import DeleteSource from './Source/DeleteSource.vue'
import EditJob from './Job/EditJob.vue'
import DeleteJob from './Job/DeleteJob.vue'
import EditLog from './Log/EditLog.vue'
import DeleteLog from './Log/DeleteLog.vue'
import EditMapping from './Mapping/EditMapping.vue'
import DeleteMapping from './Mapping/DeleteMapping.vue'
import EditSynchronization from './Synchronization/EditSynchronization.vue'
import DeleteSynchronization from './Synchronization/DeleteSynchronization.vue'

export default {
	name: 'Modals',
	components: {
	  DeleteSource,
	  EditSource,
	  TestSource,
	  DeleteJob,
	  EditJob,
	  DeleteLog,
	  EditLog,
	  DeleteMapping,
	  EditMapping,
	  DeleteSynchronization,
	  EditSynchronization,
	},
	setup() {
	  return {
			navigationStore,
	  }
	},
}
</script>
