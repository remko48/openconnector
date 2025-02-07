/* eslint-disable @typescript-eslint/no-explicit-any */
import { SafeParseReturnType, z } from 'zod'
import { TMapping } from './mapping.types'
import getValidISOstring from '../../services/getValidISOstring.js'
import ReadonlyBaseClass from '../ReadonlyBaseClass.js'
import _ from 'lodash'

export class Mapping extends ReadonlyBaseClass implements TMapping {

	public readonly id: number
	public readonly uuid: string
	public readonly reference: string
	public readonly version: string
	public readonly name: string
	public readonly description: string
	public readonly mapping: Record<string, unknown>
	public readonly unset: string[]
	public readonly cast: Record<string, unknown>
	public readonly passThrough: boolean
	public readonly dateCreated: string
	public readonly dateModified: string

	constructor(mapping: TMapping) {
		const processedMapping: TMapping = {
			id: mapping.id || null,
			uuid: mapping.uuid || '',
			reference: mapping.reference || '',
			version: mapping.version || '',
			name: mapping.name || '',
			description: mapping.description || '',
			mapping: (mapping.mapping && !Array.isArray(mapping.mapping)) ? mapping.mapping : {},
			unset: mapping.unset || [],
			cast: (mapping.cast && !Array.isArray(mapping.cast)) ? mapping.cast : {},
			passThrough: mapping.passThrough ?? true,
			dateCreated: getValidISOstring(mapping.dateCreated) ?? '',
			dateModified: getValidISOstring(mapping.dateModified) ?? '',
		}

		super(processedMapping)
	}

	public cloneRaw(): TMapping {
		return _.cloneDeep(this)
	}

	public validate(): SafeParseReturnType<TMapping, unknown> {
		const schema = z.object({
			id: z.number().nullable(),
			uuid: z.string().uuid().max(36).nullable(),
			reference: z.string().max(255),
			version: z.string().max(255),
			name: z.string().max(255),
			description: z.string(),
			mapping: z.record(z.any()),
			unset: z.array(z.string()),
			cast: z.record(z.any()),
			passThrough: z.boolean(),
			dateCreated: z.string().or(z.literal('')),
			dateModified: z.string().or(z.literal('')),
		})

		return schema.safeParse({ ...this })
	}

}
