export type TConsumer = {
    id: string
    uuid: string
    name: string
    description: string
    reference: string
    version: string
    domains: string[]
    ips: string[]
    authorizationType: 'none' | 'basic' | 'bearer' | 'apiKey' | 'oauth2' | 'jwt'
    authorizationConfiguration: string[][]
    created: string
    updated: string
}
