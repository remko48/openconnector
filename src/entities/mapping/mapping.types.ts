export type TMapping = {
    id?: string
    reference?: string | null
    version: string
    name: string
    description?: string | null
    mapping: any[]
    unset?: any[] | null
    cast?: any[] | null
    passTrough?: boolean | null
    dateCreated?: string | null
    dateModified?: string | null
}