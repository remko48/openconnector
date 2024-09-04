import { Job } from './job'
import { TJob } from './job.types'

export const mockJobData = (): TJob[] => [
    {
        id: "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f",
        name: "Daily Backup",
        description: "Performs a daily backup of the system",
        version: "1.0.0",
        crontab: "0 0 * * *",
        isEnabled: true
    },
    {
        id: "4c3edd34-a90d-4d2a-8894-adb5836ecde8",
        name: "Weekly Report",
        description: "Generates and sends weekly reports",
        version: "1.1.0",
        crontab: "0 9 * * 1",
        isEnabled: true
    }
]

export const mockJob = (data: TJob[] = mockJobData()): TJob[] => data.map(item => new Job(item))