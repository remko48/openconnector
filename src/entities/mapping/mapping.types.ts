/* eslint-disable @typescript-eslint/no-explicit-any */
export type TMapping = {
    id: string
    reference: string
    version: string
    name: string
    description: string
    mapping: any[]
    unset: any[]
    cast: any[]
    passTrough: boolean
    dateCreated: string
    dateModified: string
}
