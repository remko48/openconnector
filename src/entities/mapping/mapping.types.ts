/* eslint-disable @typescript-eslint/no-explicit-any */
export type TMapping = {
    id?: string
    reference?: string | null
    version: string
    name: string
    description?: string | null
    mapping: any[]
    unset?: any[] | null
    cast?: any[] | null
    passThrough?: boolean | null
    dateCreated?: string | null
    dateModified?: string | null
}
