export type TSynchronization = {
    id?: string
    name: string
    description: string
    sourceId: string
    sourceType: string
    sourceHash: string
    sourceTargetMapping: string
    sourceConfig: object
    targetId: string
    targetType: string
    targetHash: string
    targetSourceMapping: string
    targetConfig: object
}
