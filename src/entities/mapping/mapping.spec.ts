import { Mapping } from './mapping'
import { mockMapping } from './mapping.mock'

describe('Mapping Entity', () => {
    it('create Mapping entity with full data', () => {
        const mapping = new Mapping(mockMapping()[0])

        expect(mapping).toBeInstanceOf(Mapping)
        expect(mapping).toEqual(mockMapping()[0])

        expect(mapping.validate().success).toBe(true)
    })
})