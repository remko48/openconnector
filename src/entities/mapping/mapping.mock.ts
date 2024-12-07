import { Mapping } from './mapping'
import { TMapping } from './mapping.types'

export const mockMappingData = (): TMapping[] => [
	{
		id: 1,
		uuid: '5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f',
		name: 'User Data Mapping',
		version: '1.0.0',
		description: 'Maps user data from source to target system',
		mapping: {
			firstName: 'given_name',
			lastName: 'family_name',
			email: 'email_address',
		},
		passThrough: true,
		reference: '',
		unset: [],
		cast: {},
		dateCreated: '',
		dateModified: '',
	},
	{
		id: 2,
		uuid: '5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f',
		name: 'Product Mapping',
		version: '1.1.0',
		description: 'Maps product data between systems',
		mapping: {
			productName: 'name',
			productPrice: 'price',
			productDescription: 'description',
		},
		passThrough: false,
		unset: ['internal_id', 'created_by'],
		cast: {
			price: 'float',
			inStock: 'boolean',
		},
		reference: '',
		dateCreated: '',
		dateModified: '',
	},
]

export const mockMapping = (data: TMapping[] = mockMappingData()): TMapping[] => data.map(item => new Mapping(item))
