import { setActivePinia, createPinia } from 'pinia'

import { useConsumerStore } from './consumer'
import { Consumer, mockConsumer } from '../../entities/index.js'

describe('Consumer Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets consumer item correctly', () => {
		const store = useConsumerStore()

		store.setConsumerItem(mockConsumer()[0])

		expect(store.getConsumerItem()).toBeInstanceOf(Consumer)
		expect(store.getConsumerItem()).toEqual(mockConsumer()[0])

		expect(store.getConsumerItem().validate().success).toBe(true)
	})

	it('sets consumer list correctly', () => {
		const store = useConsumerStore()

		store.setConsumerList(mockConsumer())

		expect(store.getConsumerList()).toHaveLength(mockConsumer().length)

		store.getConsumerList().forEach((item, index) => {
			expect(item).toBeInstanceOf(Consumer)
			expect(item).toEqual(mockConsumer()[index])
			expect(item.validate().success).toBe(true)
		})
	})

	// ... other tests ...
})
