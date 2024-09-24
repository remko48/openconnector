export type TWebhook = {
    id?: string
    name: string
    description?: string | null
    version: string
    url: string
    isEnabled?: boolean
    dateCreated?: string | null
    dateModified?: string | null
    headers?: object | null
    events?: string[]
    retryPolicy?: object | null
    timeout?: number
    lastTriggered?: string | null
    lastResponse?: {
        status: number,
        body: string
    } | null
    secretKey?: string | null
    payloadFormat?: 'json' | 'xml' | 'form-data'
    active?: boolean
}
