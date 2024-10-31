/* eslint-disable @typescript-eslint/no-explicit-any */
export type TMapping = {
    id: number
    uuid: string
    reference: string
    version: string
    name: string
    description: string
    mapping: Record<string, unknown>
    unset: any[]
    cast: Record<string, unknown>
    passThrough: boolean
    dateCreated: string
    dateModified: string
}
