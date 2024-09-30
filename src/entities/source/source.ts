/* eslint-disable @typescript-eslint/no-explicit-any */
import { SafeParseReturnType, z } from 'zod'
import { TSource } from './source.types'

export class Source implements TSource {

	public id: string
	public name: string
	public description: string | null
	public reference: string | null
	public version: string
	public location: string
	public isEnabled: boolean
	public type: 'json' | 'xml' | 'soap' | 'ftp' | 'sftp'
	public authorizationHeader: string
	public auth: 'apikey' | 'jwt' | 'username-password' | 'none' | 'jwt-HS256' | 'vrijbrp-jwt' | 'pink-jwt' | 'oauth'
	public authenticationConfig: object | null
	public authorizationPassthroughMethod: 'header' | 'query' | 'form_params' | 'json' | 'base_auth'
	public locale: string | null
	public accept: string | null
	public jwt: string | null
	public jwtId: string | null
	public secret: string | null
	public username: string | null
	public password: string | null
	public apikey: string | null
	public documentation: string | null
	public loggingConfig: object
	public oas: any[] | null
	public paths: any[] | null
	public headers: any[] | null
	public translationConfig: any[]
	public configuration: object | null
	public endpointsConfig: object | null
	public status: string
	public logRetention: number
	public errorRetention: number
	public lastCall: string | null
	public lastSync: string | null
	public objectCount: number
	public dateCreated: string | null
	public dateModified: string | null
	public test: boolean

	constructor(source: TSource) {
		this.id = source.id || ''
		this.name = source.name || ''
		this.description = source.description || null
		this.reference = source.reference || null
		this.version = source.version || '0.0.0'
		this.location = source.location || ''
		this.isEnabled = source.isEnabled ?? true
		this.type = source.type || 'json'
		this.authorizationHeader = source.authorizationHeader || 'Authorization'
		this.auth = source.auth || 'none'
		this.authenticationConfig = source.authenticationConfig || null
		this.authorizationPassthroughMethod = source.authorizationPassthroughMethod || 'header'
		this.locale = source.locale || null
		this.accept = source.accept || null
		this.jwt = source.jwt || null
		this.jwtId = source.jwtId || null
		this.secret = source.secret || null
		this.username = source.username || null
		this.password = source.password || null
		this.apikey = source.apikey || null
		this.documentation = source.documentation || null
		this.loggingConfig = source.loggingConfig || {}
		this.oas = source.oas || null
		this.paths = source.paths || null
		this.headers = source.headers || null
		this.translationConfig = source.translationConfig || []
		this.configuration = source.configuration || null
		this.endpointsConfig = source.endpointsConfig || null
		this.status = source.status || 'No calls have been made yet to this source'
		this.logRetention = source.logRetention || 3600
		this.errorRetention = source.errorRetention || 86400
		this.lastCall = source.lastCall || null
		this.lastSync = source.lastSync || null
		this.objectCount = source.objectCount || 0
		this.dateCreated = source.dateCreated || null
		this.dateModified = source.dateModified || null
		this.test = source.test || false
	}

	public validate(): SafeParseReturnType<TSource, unknown> {
		const schema = z.object({
			id: z.string().uuid(),
			name: z.string().max(255),
			location: z.string().max(255),
			type: z.enum(['json', 'xml', 'soap', 'ftp', 'sftp']),
			auth: z.enum(['apikey', 'jwt', 'username-password', 'none', 'jwt-HS256', 'vrijbrp-jwt', 'pink-jwt', 'oauth']),
		})

		return schema.safeParse({ ...this })
	}

}
