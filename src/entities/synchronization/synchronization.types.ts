export type TSynchronization = {
    id?: string
    entity: object
    object?: object | null
    action?: object | null
    gateway?: object | null
    sourceObject?: object | null
    endpoint?: string | null
    sourceId?: string | null
    hash?: string | null
    sha?: string | null
    blocked?: boolean
    sourceLastChanged?: string | null
    lastChecked?: string | null
    lastSynced?: string | null
    dateCreated?: string | null
    dateModified?: string | null
    tryCounter?: number
    dontSyncBefore?: string | null
    mapping?: object | null
}