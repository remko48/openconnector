import { Endpoint } from './endpoint'
import { TEndpoint } from './endpoint.types'

export const mockEndpointData = (): TEndpoint[] => [
	{
		id: '5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f',
		name: 'User Authentication',
		description: 'Handles user authentication requests',
		version: '1.0.0',
		path: '/api/auth',
		method: 'POST',
		isEnabled: true,
	},
	{
		id: '4c3edd34-a90d-4d2a-8894-adb5836ecde8',
		name: 'Get User Profile',
		description: 'Retrieves user profile information',
		version: '1.1.0',
		path: '/api/user/profile',
		method: 'GET',
		isEnabled: true,
	},
]

export const mockEndpoint = (data: TEndpoint[] = mockEndpointData()): TEndpoint[] => data.map(item => new Endpoint(item))
