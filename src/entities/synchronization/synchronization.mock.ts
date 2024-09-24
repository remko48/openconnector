import { Synchronization } from './synchronization'
import { TSynchronization } from './synchronization.types'

export const mockSynchronizationData = (): TSynchronization[] => [
	{
		id: '5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f',
		entity: { id: 'entity1', name: 'Entity 1' },
		object: { id: 'object1', name: 'Object 1' },
		sourceId: 'source1',
		blocked: false,
		tryCounter: 0,
	},
	{
		id: '4c3edd34-a90d-4d2a-8894-adb5836ecde8',
		entity: { id: 'entity2', name: 'Entity 2' },
		object: { id: 'object2', name: 'Object 2' },
		sourceId: 'source2',
		blocked: true,
		tryCounter: 3,
	},
]

export const mockSynchronization = (data: TSynchronization[] = mockSynchronizationData()): TSynchronization[] => data.map(item => new Synchronization(item))
