import { Source } from './source'
import { TSource } from './source.types'

export const mockSourceData = (): TSource[] => [
    {
        id: "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f",
        name: "Test Source 1",
        description: "A test source for demonstration",
        location: "https://api.test1.com",
        type: "json",
        auth: "apikey",
        apikey: "test-api-key-1"
    },
    {
        id: "4c3edd34-a90d-4d2a-8894-adb5836ecde8",
        name: "Test Source 2",
        description: "Another test source",
        location: "https://api.test2.com",
        type: "xml",
        auth: "jwt",
        jwt: "test-jwt-token"
    }
]

export const mockSource = (data: TSource[] = mockSourceData()): TSource[] => data.map(item => new Source(item))