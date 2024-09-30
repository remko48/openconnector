export type TJob = {
    id?: string
    name: string
    description?: string | null
    jobClass?: string
    arguments?: object | null
    interval?: number
    executionTime?: number
    timeSensitive?: boolean
    allowParallelRuns?: boolean
    isEnabled?: boolean
    singleRun?: boolean
    scheduleAfter?: string | null
    userId?: string | null
    jobListId?: string | null
    logRetention?: number
    errorRetention?: number
    lastRun?: string | null
    nextRun?: string | null
    created?: string | null
    updated?: string | null
}
