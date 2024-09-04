import { Mapping } from './mapping'
import { TMapping } from './mapping.types'

export const mockMappingData = (): TMapping[] => [
    {
        id: "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f",
        name: "User Data Mapping",
        version: "1.0.0",
        description: "Maps user data from source to target system",
        mapping: [
            { source: "firstName", target: "given_name" },
            { source: "lastName", target: "family_name" },
            { source: "email", target: "email_address" }
        ],
        passTrough: true
    },
    {
        id: "4c3edd34-a90d-4d2a-8894-adb5836ecde8",
        name: "Product Mapping",
        version: "1.1.0",
        description: "Maps product data between systems",
        mapping: [
            { source: "productName", target: "name" },
            { source: "productPrice", target: "price" },
            { source: "productDescription", target: "description" }
        ],
        passTrough: false,
        unset: ["internal_id", "created_by"],
        cast: [
            { field: "price", type: "float" },
            { field: "inStock", type: "boolean" }
        ]
    }
]

export const mockMapping = (data: TMapping[] = mockMappingData()): TMapping[] => data.map(item => new Mapping(item))