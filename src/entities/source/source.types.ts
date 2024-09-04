export type TSource = {
    id?: string
    name: string
    description?: string | null
    reference?: string | null
    version?: string
    location: string
    isEnabled?: boolean
    type: 'json' | 'xml' | 'soap' | 'ftp' | 'sftp'
    authorizationHeader?: string
    auth: 'apikey' | 'jwt' | 'username-password' | 'none' | 'jwt-HS256' | 'vrijbrp-jwt' | 'pink-jwt' | 'oauth'
    authenticationConfig?: object | null
    authorizationPassthroughMethod?: 'header' | 'query' | 'form_params' | 'json' | 'base_auth'
    locale?: string | null
    accept?: string | null
    jwt?: string | null
    jwtId?: string | null
    secret?: string | null
    username?: string | null
    password?: string | null
    apikey?: string | null
    documentation?: string | null
    loggingConfig?: object
    oas?: any[] | null
    paths?: any[] | null
    headers?: any[] | null
    translationConfig?: any[]
    configuration?: object | null
    endpointsConfig?: object | null
    status?: string
    lastCall?: string | null
    lastSync?: string | null
    objectCount?: number
    dateCreated?: string | null
    dateModified?: string | null
    test?: boolean
}