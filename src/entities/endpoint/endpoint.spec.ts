import { Endpoint } from './endpoint'
import { mockEndpoint } from './endpoint.mock'

describe('Endpoint Entity', () => {
	it('create Endpoint entity with full data', () => {
		const endpoint = new Endpoint(mockEndpoint()[0])

		expect(endpoint).toBeInstanceOf(Endpoint)
		expect(endpoint).toEqual(mockEndpoint()[0])

		expect(endpoint.validate().success).toBe(true)
	})

	it('create Endpoint entity with partial data', () => {
		const endpoint = new Endpoint(mockEndpoint()[1])

		expect(endpoint).toBeInstanceOf(Endpoint)
		expect(endpoint).toEqual(mockEndpoint()[1])

		expect(endpoint.validate().success).toBe(true)
	})
})
