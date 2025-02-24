# Source

A Source in Open Connector represents a digital endpoint that can be used for data exchange. Sources can function both as data providers and destinations, enabling bidirectional data flow. The actual data transfer process is managed through Synchronizations.

## Types of Sources

Open Connector supports three main types of sources:

1. **Nextcloud Files**
   - Internal file system integration
   - Direct access to files within your Nextcloud environment

2. **External APIs**
   - REST/HTTP-based services
   - Third-party application interfaces
   - Cloud services
   - External files like CSV, JSON, XML, etc.

3. **External Databases** (Deprecated)
   > ⚠️ **Note:** Database connections are deprecated. For database interactions, we recommend using [Open Registers](https://open-registers.com) as a more robust solution.

## Core Concepts

### Purpose
A Source defines:
- The location of the data endpoint
- Authentication methods
- Connection parameters
- Basic interaction rules

### Authentication
Sources can use various authentication methods:
- API Keys
- OAuth2
- Basic Authentication
- Custom Headers

## Procces 
Calls to a source are made trought the open conncetor call service, this is essentiall a wrapper around the guzzle http client. But it gives some adtional features like logging, caching and error handling and dealing with pagination ans xml files.

## Configuration

Sources have the following configurable properties:

| Property | Type | Description | Default |
|----------|------|-------------|---------|
| uuid | string | Unique identifier for the source | null |
| name | string | Display name of the source | null |
| description | string | Detailed description of the source | null |
| reference | string | External reference identifier | null |
| version | string | Version number of the source | '0.0.0' |
| location | string | URL or path to the source | null |
| isEnabled | boolean | Whether the source is active | null |
| type | string | Type of source (api, file or database) | null |
| loggingConfig | array | Logging configuration | null |
| configuration | array | General configuration | null |
| rateLimitLimit | integer | Total allowed requests per period | null |
| rateLimitRemaining | integer | Remaining allowed requests | null |
| rateLimitReset | integer | Unix timestamp for limit reset | null |
| rateLimitWindow | integer | Seconds between requests | null |
| lastCall | datetime | Timestamp of last API call | null |
| lastSync | datetime | Timestamp of last sync | null |
| dateCreated | datetime | Creation timestamp | null |
| dateModified | datetime | Last modified timestamp | null |


Sources are configured using Guzzle HTTP client options. These settings control how Open Connector interacts with the source. That means that the configuration is passed as an array to the Guzzle client with one aditiondal option and thats that the method of the call can be set trought the method property (defaults to `GET`). The baser_uri is overwritten by the location property of the source.

### Example Source Configurations

```json
{
    "base_uri": "location-from-source",
    "method": "GET",
    "headers": {
        'Authorization' => 'Bearer your-token',
        'Accept' => 'application/json',
    ],
    'timeout' => 30,
]
```

For detailed configuration options, refer to the [Guzzle Documentation](https://docs.guzzlephp.org/en/stable/request-options.html).



## Best Practices

1. **Security**
   - Always use environment variables for sensitive credentials
   - Implement proper error handling for connection failures
   - Use appropriate timeout values

2. **Performance**
   - Configure appropriate cache settings
   - Use pagination when dealing with large datasets
   - Set reasonable timeout values

3. **Maintenance**
   - Regularly validate source connections
   - Monitor API rate limits
   - Keep authentication credentials up to date

## Related Concepts

- **Synchronizations**: Define how data flows between sources
- **Transformations**: Specify how data should be modified during transfer
- **Mappings**: Define relationships between source and destination data structures