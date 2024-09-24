export type TEndpoint = {
    id?: string
    name: string
    description?: string | null
    version: string
    path: string
    method: string
    isEnabled?: boolean
    dateCreated?: string | null
    dateModified?: string | null
    headers?: object | null
    parameters?: object | null
    responseSchema?: object | null
    authentication?: string | null
    rateLimit?: number | null
    caching?: boolean
    timeout?: number | null
}
