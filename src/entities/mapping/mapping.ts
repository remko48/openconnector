/* eslint-disable @typescript-eslint/no-explicit-any */
import { SafeParseReturnType, z } from 'zod'
import { TMapping } from './mapping.types'
import getValidISOstring from '../../services/getValidISOstring.js'
import ReadonlyBaseClass from '../ReadonlyBaseClass.js'

export class Mapping extends ReadonlyBaseClass implements TMapping {

	public readonly id: string
	public readonly reference: string
	public readonly version: string
	public readonly name: string
	public readonly description: string
	public readonly mapping: any[]
	public readonly unset: any[]
	public readonly cast: any[]
	public readonly passTrough: boolean
	public readonly dateCreated: string
	public readonly dateModified: string

	constructor(mapping: TMapping) {
		const processedMapping: TMapping = {
			id: mapping.id || null,
			reference: mapping.reference || '',
			version: mapping.version || '',
			name: mapping.name || '',
			description: mapping.description || '',
			mapping: mapping.mapping || [],
			unset: mapping.unset || [],
			cast: mapping.cast || [],
			passTrough: mapping.passTrough ?? true,
			dateCreated: getValidISOstring(mapping.dateCreated) ?? '',
			dateModified: getValidISOstring(mapping.dateModified) ?? '',
		}

		super(processedMapping)
	}

	public validate(): SafeParseReturnType<TMapping, unknown> {
		const schema = z.object({
			id: z.string().nullable(),
			reference: z.string(),
			version: z.string(),
			name: z.string(),
			description: z.string(),
			mapping: z.array(z.any()),
			unset: z.array(z.any()),
			cast: z.array(z.any()),
			passTrough: z.boolean(),
			dateCreated: z.string().datetime().or(z.literal('')),
			dateModified: z.string().datetime().or(z.literal('')),
		})

		return schema.safeParse({ ...this })
	}

}
