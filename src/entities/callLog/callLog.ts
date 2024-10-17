/* eslint-disable @typescript-eslint/no-explicit-any */
import { SafeParseReturnType, z } from 'zod'
import { TCallLog } from './callLog.types'
import ReadonlyBaseClass from '../ReadonlyBaseClass.js'

export class CallLog extends ReadonlyBaseClass implements TCallLog {

	public readonly id?: string
	public readonly sourceId: string
	public readonly endpoint: string
	public readonly method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH'
	public readonly statusCode: number
	public readonly requestHeaders?: object
	public readonly requestBody?: any
	public readonly responseHeaders?: object
	public readonly responseBody?: any
	public readonly duration: number
	public readonly error?: string | null
	public readonly created: string
	public readonly updated?: string | null

	constructor(callLog: TCallLog) {
		const processedCallLog = {
			id: callLog.id,
			sourceId: callLog.sourceId,
			endpoint: callLog.endpoint,
			method: callLog.method,
			statusCode: callLog.statusCode,
			requestHeaders: callLog.requestHeaders,
			requestBody: callLog.requestBody,
			responseHeaders: callLog.responseHeaders,
			responseBody: callLog.responseBody,
			duration: callLog.duration,
			error: callLog.error || null,
			created: callLog.created,
			updated: callLog.updated || null,
		}

		super(processedCallLog)
	}

	public validate(): SafeParseReturnType<TCallLog, unknown> {
		const schema = z.object({
			id: z.string().uuid().optional(),
			sourceId: z.string().uuid(),
			endpoint: z.string(),
			method: z.enum(['GET', 'POST', 'PUT', 'DELETE', 'PATCH']),
			statusCode: z.number().int().positive(),
			requestHeaders: z.record(z.any()).optional(),
			requestBody: z.any().optional(),
			responseHeaders: z.record(z.any()).optional(),
			responseBody: z.any().optional(),
			duration: z.number().positive(),
			error: z.string().nullable().optional(),
			created: z.string().datetime(),
			updated: z.string().datetime().nullable().optional(),
		})

		return schema.safeParse({ ...this })
	}

}
