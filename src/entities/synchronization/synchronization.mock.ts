import { Synchronization } from './synchronization'
import { TSynchronization } from './synchronization.types'

export const mockSynchronizationData = (): TSynchronization[] => [
	{
		id: '5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f',
		name: 'Synchronization 1',
		description: 'Synchronization 1',
		sourceId: 'source1',
		sourceType: 'api',
		sourceHash: 'source1',
		sourceTargetMapping: 'source1',
		sourceConfig: {},
		targetId: 'target1',
		targetType: 'api',
		targetHash: 'target1',
		targetSourceMapping: 'target1',
		targetConfig: undefined,
	},
	{
		id: '4c3edd34-a90d-4d2a-8894-adb5836ecde8',
		name: 'Synchronization 2',
		description: 'Synchronization 2',
		sourceId: 'source2',
		sourceType: 'api',
		sourceHash: 'source2',
		sourceTargetMapping: 'source2',
		sourceConfig: {},
		targetId: 'target2',
		targetType: 'api',
		targetHash: 'target2',
		targetSourceMapping: '',
		targetConfig: undefined,
	},
]

export const mockSynchronization = (data: TSynchronization[] = mockSynchronizationData()): TSynchronization[] => data.map(item => new Synchronization(item))
