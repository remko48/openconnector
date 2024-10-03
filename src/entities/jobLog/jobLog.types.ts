export type TJobLog = {
    id?: string
    jobId?: string
    jobListId?: string
    jobClass?: string
    arguments?: object | null
    executionTime?: number
    userId?: string | null
    lastRun?: string | null
    nextRun?: string | null
    created?: string | null
}
