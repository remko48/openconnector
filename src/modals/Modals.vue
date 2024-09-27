<template>
	<div>
		<DeleteSource />
		<EditSource />
		<DeleteJob />
		<EditJob />
		<DeleteLog />
		<EditLog />
		<DeleteMapping />
		<EditMapping />
		<EditMappingMapping />
		<DeleteMappingMapping />
		<DeleteSynchronization />
		<EditSynchronization />
	</div>
</template>

<script>
import { navigationStore } from '../store/store.js'
import EditSource from './Source/EditSource.vue'
import DeleteSource from './Source/DeleteSource.vue'
import EditJob from './Job/EditJob.vue'
import DeleteJob from './Job/DeleteJob.vue'
import EditLog from './Log/EditLog.vue'
import DeleteLog from './Log/DeleteLog.vue'
import EditMapping from './Mapping/EditMapping.vue'
import DeleteMapping from './Mapping/DeleteMapping.vue'
import EditSynchronization from './Synchronization/EditSynchronization.vue'
import DeleteSynchronization from './Synchronization/DeleteSynchronization.vue'
import EditMappingMapping from './mappingMapping/EditMappingMapping.vue'
import DeleteMappingMapping from './mappingMapping/DeleteMappingMapping.vue'

export default {
	name: 'Modals',
	components: {
	  DeleteSource,
	  EditSource,
	  DeleteJob,
	  EditJob,
	  DeleteLog,
	  EditLog,
	  DeleteMapping,
	  EditMapping,
	  DeleteSynchronization,
	  EditSynchronization,
	  EditMappingMapping,
	  DeleteMappingMapping,
	},
	setup() {
	  return {
			navigationStore,
	  }
	},
}
</script>
