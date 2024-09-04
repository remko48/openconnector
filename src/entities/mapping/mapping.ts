import { SafeParseReturnType, z } from 'zod'
import { TMapping } from './mapping.types'

export class Mapping implements TMapping {
    public id: string
    public reference: string | null
    public version: string
    public name: string
    public description: string | null
    public mapping: any[]
    public unset: any[] | null
    public cast: any[] | null
    public passTrough: boolean | null
    public dateCreated: string | null
    public dateModified: string | null

    constructor(mapping: TMapping) {
        this.id = mapping.id || ''
        this.reference = mapping.reference || null
        this.version = mapping.version || '0.0.0'
        this.name = mapping.name || ''
        this.description = mapping.description || null
        this.mapping = mapping.mapping || []
        this.unset = mapping.unset || null
        this.cast = mapping.cast || null
        this.passTrough = mapping.passTrough ?? true
        this.dateCreated = mapping.dateCreated || null
        this.dateModified = mapping.dateModified || null
    }

    public validate(): SafeParseReturnType<TMapping, unknown> {
        const schema = z.object({
            id: z.string().uuid(),
            name: z.string().max(255),
            version: z.string(),
            mapping: z.array(z.any())
        })

        return schema.safeParse({ ...this })
    }
}