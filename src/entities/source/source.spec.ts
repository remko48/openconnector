import { Source } from './source'
import { mockSource } from './source.mock'

describe('Source Entity', () => {
    it('create Source entity with full data', () => {
        const source = new Source(mockSource()[0])

        expect(source).toBeInstanceOf(Source)
        expect(source).toEqual(mockSource()[0])

        expect(source.validate().success).toBe(true)
    })

    it('create Source entity with partial data', () => {
        const source = new Source(mockSource()[1])

        expect(source).toBeInstanceOf(Source)
        expect(source).toEqual(mockSource()[1])

        expect(source.validate().success).toBe(true)
    })
})