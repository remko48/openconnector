export type TLog = {
    id?: string
    type: 'in' | 'out'
    callId: string
    requestMethod: string
    requestHeaders: object[]
    requestQuery: object[]
    requestPathInfo: string
    requestLanguages: string[]
    requestServer: object
    requestContent: string
    responseStatus?: string | null
    responseStatusCode?: number | null
    responseHeaders?: object[] | null
    responseContent?: string | null
    userId?: string | null
    session: string
    sessionValues: object
    responseTime: number
    routeName?: string | null
    routeParameters?: object | null
    entity?: object | null
    endpoint?: object | null
    gateway?: object | null
    handler?: object | null
    objectId?: string | null
    dateCreated?: string | null
    dateModified?: string | null
}
