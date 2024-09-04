import { SafeParseReturnType, z } from 'zod'
import { TLog } from './log.types'

export class Log implements TLog {
    public id: string
    public type: 'in' | 'out'
    public callId: string
    public requestMethod: string
    public requestHeaders: object[]
    public requestQuery: object[]
    public requestPathInfo: string
    public requestLanguages: string[]
    public requestServer: object
    public requestContent: string
    public responseStatus: string | null
    public responseStatusCode: number | null
    public responseHeaders: object[] | null
    public responseContent: string | null
    public userId: string | null
    public session: string
    public sessionValues: object
    public responseTime: number
    public routeName: string | null
    public routeParameters: object | null
    public entity: object | null
    public endpoint: object | null
    public gateway: object | null
    public handler: object | null
    public objectId: string | null
    public dateCreated: string | null
    public dateModified: string | null

    constructor(log: TLog) {
        this.id = log.id || ''
        this.type = log.type || 'in'
        this.callId = log.callId || ''
        this.requestMethod = log.requestMethod || ''
        this.requestHeaders = log.requestHeaders || []
        this.requestQuery = log.requestQuery || []
        this.requestPathInfo = log.requestPathInfo || ''
        this.requestLanguages = log.requestLanguages || []
        this.requestServer = log.requestServer || {}
        this.requestContent = log.requestContent || ''
        this.responseStatus = log.responseStatus || null
        this.responseStatusCode = log.responseStatusCode || null
        this.responseHeaders = log.responseHeaders || null
        this.responseContent = log.responseContent || null
        this.userId = log.userId || null
        this.session = log.session || ''
        this.sessionValues = log.sessionValues || {}
        this.responseTime = log.responseTime || 0
        this.routeName = log.routeName || null
        this.routeParameters = log.routeParameters || null
        this.entity = log.entity || null
        this.endpoint = log.endpoint || null
        this.gateway = log.gateway || null
        this.handler = log.handler || null
        this.objectId = log.objectId || null
        this.dateCreated = log.dateCreated || null
        this.dateModified = log.dateModified || null
    }

    public validate(): SafeParseReturnType<TLog, unknown> {
        const schema = z.object({
            id: z.string().uuid(),
            type: z.enum(['in', 'out']),
            callId: z.string().uuid(),
            requestMethod: z.string().max(255),
            requestHeaders: z.array(z.object({})),
            requestQuery: z.array(z.object({})),
            requestPathInfo: z.string().max(255),
            requestLanguages: z.array(z.string()),
            requestServer: z.object({}),
            requestContent: z.string(),
            session: z.string().max(255),
            sessionValues: z.object({}),
            responseTime: z.number().int()
        })

        return schema.safeParse({ ...this })
    }
}