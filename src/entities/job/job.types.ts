export type TJob = {
    id?: string
    name: string
    description?: string | null
    reference?: string | null
    version: string
    crontab?: string
    userId?: string | null
    throws?: string[]
    data?: object | null
    lastRun?: string | null
    nextRun?: string | null
    isEnabled?: boolean | null
    dateCreated?: string | null
    dateModified?: string | null
    listens?: string[]
    conditions?: object | null
    class?: string | null
    priority?: number
    async?: boolean
    configuration?: object | null
    isLockable?: boolean
    locked?: string | null
    lastRunTime?: number | null
    status?: boolean | null
    actionHandlerConfiguration?: object | null
}
