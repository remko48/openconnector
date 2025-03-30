<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Source extends Entity implements JsonSerializable
{

    /**
     * The unique identifier of the source.
     *
     * @var string|null
     */
    protected ?string $uuid = null;

    /**
     * The name of the source.
     *
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * The description of the source.
     *
     * @var string|null
     */
    protected ?string $description = null;

    /**
     * The reference identifier for the source.
     *
     * @var string|null
     */
    protected ?string $reference = null;

    /**
     * The version of the source.
     *
     * @var string|null
     */
    protected ?string $version = '0.0.0';

    /**
     * The location of the source.
     *
     * @var string|null
     */
    protected ?string $location = null;

    /**
     * Indicates if the source is enabled.
     *
     * @var bool|null
     */
    protected ?bool $isEnabled = null;

    /**
     * The type of the source.
     *
     * @var string|null
     */
    protected ?string $type = null;

    /**
     * The authorization header for the source.
     *
     * @var string|null
     */
    protected ?string $authorizationHeader = null;

    /**
     * The authentication method for the source.
     *
     * @var string|null
     */
    protected ?string $auth = null;

    /**
     * The authentication configuration for the source.
     *
     * @var array|null
     */
    protected ?array $authenticationConfig = [];

    /**
     * The method for authorization passthrough.
     *
     * @var string|null
     */
    protected ?string $authorizationPassthroughMethod = null;

    /**
     * The locale setting for the source.
     *
     * @var string|null
     */
    protected ?string $locale = null;

    /**
     * The accept header for the source.
     *
     * @var string|null
     */
    protected ?string $accept = null;

    /**
     * The JSON Web Token (JWT) for the source.
     *
     * @var string|null
     */
    protected ?string $jwt = null;

    /**
     * The identifier for the JWT.
     *
     * @var string|null
     */
    protected ?string $jwtId = null;

    /**
     * The secret key for the source.
     *
     * @var string|null
     */
    protected ?string $secret = null;

    /**
     * The username for authentication.
     *
     * @var string|null
     */
    protected ?string $username = null;

    /**
     * The password for authentication.
     *
     * @var string|null
     */
    protected ?string $password = null;

    /**
     * The API key for the source.
     *
     * @var string|null
     */
    protected ?string $apikey = null;

    /**
     * The documentation URL or content for the source.
     *
     * @var string|null
     */
    protected ?string $documentation = null;

    /**
     * The logging configuration for the source.
     *
     * @var array|null
     */
    protected ?array $loggingConfig = [];

    /**
     * The OpenAPI Specification (OAS) for the source.
     *
     * @var string|null
     */
    protected ?string $oas = null;

    /**
     * The paths configuration for the source.
     *
     * @var array|null
     */
    protected ?array $paths = [];

    /**
     * The headers configuration for the source.
     *
     * @var array|null
     */
    protected ?array $headers = [];

    /**
     * The translation configuration for the source.
     *
     * @var array|null
     */
    protected ?array $translationConfig = [];

    /**
     * The general configuration for the source.
     *
     * @var array|null
     */
    protected ?array $configuration = [];

    /**
     * The endpoints configuration for the source.
     *
     * @var array|null
     */
    protected ?array $endpointsConfig = [];

    /**
     * The status of the source.
     *
     * @var string|null
     */
    protected ?string $status = null;

    /**
     * The duration in seconds to retain all logs.
     *
     * @var int
     */
    protected ?int $logRetention = 3600;

    /**
     * The duration in seconds to retain error logs.
     *
     * @var int
     */
    protected ?int $errorRetention = 86400;

    /**
     * The count of objects associated with the source.
     *
     * @var int|null
     */
    protected ?int $objectCount = null;

    /**
     * Indicates if the source is in test mode.
     *
     * @var bool|null
     */
    protected ?bool $test = null;

    /**
     * The total number of allowed requests within a specific time period.
     *
     * @var int|null
     */
    protected ?int $rateLimitLimit = null;

    /**
     * Specifies how many requests are still allowed within the current limit.
     *
     * @var int|null
     */
    protected ?int $rateLimitRemaining = null;

    /**
     * A Unix Time Stamp that indicates when the rate limit will be reset.
     *
     * @var int|null
     */
    protected ?int $rateLimitReset = null;

    /**
     * Indicates how many seconds the client must wait before making new requests.
     *
     * @var int|null
     */
    protected ?int $rateLimitWindow = null;

    /**
     * The date and time of the last call made to the source.
     *
     * @var DateTime|null
     */
    protected ?DateTime $lastCall = null;

    /**
     * The date and time of the last synchronization with the source.
     *
     * @var DateTime|null
     */
    protected ?DateTime $lastSync = null;

    /**
     * The date and time the source was created.
     *
     * @var DateTime|null
     */
    protected ?DateTime $dateCreated = null;

    /**
     * The date and time the source was last modified.
     *
     * @var DateTime|null
     */
    protected ?DateTime $dateModified = null;


    /**
     * Get the authentication configuration
     *
     * @return array The authentication configuration or empty array if null
     */
    public function getAuthenticationConfig(): array
    {
        return ($this->authenticationConfig ?? []);

    }//end getAuthenticationConfig()


    /**
     * Get the logging configuration
     *
     * @return array The logging configuration or empty array if null
     */
    public function getLoggingConfig(): array
    {
        return ($this->loggingConfig ?? []);

    }//end getLoggingConfig()


    /**
     * Get the paths array
     *
     * @return array The paths or empty array if null
     */
    public function getPaths(): array
    {
        return ($this->paths ?? []);

    }//end getPaths()


    /**
     * Get the headers array
     *
     * @return array The headers or empty array if null
     */
    public function getHeaders(): array
    {
        return ($this->headers ?? []);

    }//end getHeaders()


    /**
     * Get the translation configuration
     *
     * @return array The translation configuration or empty array if null
     */
    public function getTranslationConfig(): array
    {
        return ($this->translationConfig ?? []);

    }//end getTranslationConfig()


    /**
     * Get the general configuration
     *
     * @return array The configuration or empty array if null
     */
    public function getConfiguration(): array
    {
        return ($this->configuration ?? []);

    }//end getConfiguration()


    /**
     * Get the endpoints configuration
     *
     * @return array The endpoints configuration or empty array if null
     */
    public function getEndpointsConfig(): array
    {
        return ($this->endpointsConfig ?? []);

    }//end getEndpointsConfig()


    public function __construct()
    {
        $this->addType('uuid', 'string');
        $this->addType('name', 'string');
        $this->addType('description', 'string');
        $this->addType('reference', 'string');
        $this->addType('version', 'string');
        $this->addType('location', 'string');
        $this->addType('isEnabled', 'boolean');
        $this->addType('type', 'string');
        $this->addType('authorizationHeader', 'string');
        $this->addType('auth', 'string');
        $this->addType('authenticationConfig', 'json');
        $this->addType('authorizationPassthroughMethod', 'string');
        $this->addType('locale', 'string');
        $this->addType('accept', 'string');
        $this->addType('jwt', 'string');
        $this->addType('jwtId', 'string');
        $this->addType('secret', 'string');
        $this->addType('username', 'string');
        $this->addType('password', 'string');
        $this->addType('apikey', 'string');
        $this->addType('documentation', 'string');
        $this->addType('loggingConfig', 'json');
        $this->addType('oas', 'string');
        $this->addType('paths', 'json');
        $this->addType('headers', 'json');
        $this->addType('translationConfig', 'json');
        $this->addType('configuration', 'json');
        $this->addType('endpointsConfig', 'json');
        $this->addType('status', 'string');
        $this->addType('logRetention', 'integer');
        $this->addType('errorRetention', 'integer');
        $this->addType('objectCount', 'integer');
        $this->addType('test', 'boolean');
        $this->addType('rateLimitLimit', 'integer');
        $this->addType('rateLimitRemaining', 'integer');
        $this->addType('rateLimitReset', 'integer');
        $this->addType('rateLimitWindow', 'integer');
        $this->addType('lastCall', 'datetime');
        $this->addType('lastSync', 'datetime');
        $this->addType('dateCreated', 'datetime');
        $this->addType('dateModified', 'datetime');

    }//end __construct()


    public function getJsonFields(): array
    {
        return array_keys(
            array_filter(
                $this->getFieldTypes(),
                function ($field) {
                        return $field === 'json';
                }
            )
        );

    }//end getJsonFields()


    public function hydrate(array $object): self
    {
        $jsonFields = $this->getJsonFields();

        foreach ($object as $key => $value) {
            if (in_array($key, $jsonFields) === true && $value === []) {
                $value = [];
            }

            $method = 'set'.ucfirst($key);

            try {
                $this->$method($value);
            } catch (\Exception $exception) {
                // Error handling can be added here
            }
        }

        return $this;

    }//end hydrate()


    public function jsonSerialize(): array
    {
        return [
            'id'                             => $this->id,
            'uuid'                           => $this->uuid,
            'name'                           => $this->name,
            'description'                    => $this->description,
            'version'                        => $this->version,
            'reference'                      => $this->reference,
            'location'                       => $this->location,
            'isEnabled'                      => $this->isEnabled,
            'type'                           => $this->type,
            'authorizationHeader'            => $this->authorizationHeader,
            'auth'                           => $this->auth,
            'authenticationConfig'           => $this->authenticationConfig,
            'authorizationPassthroughMethod' => $this->authorizationPassthroughMethod,
            'locale'                         => $this->locale,
            'accept'                         => $this->accept,
            'jwt'                            => $this->jwt,
            'jwtId'                          => $this->jwtId,
            'secret'                         => $this->secret,
            'username'                       => $this->username,
            'password'                       => $this->password,
            'apikey'                         => $this->apikey,
            'documentation'                  => $this->documentation,
            'loggingConfig'                  => $this->loggingConfig,
            'oas'                            => $this->oas,
            'paths'                          => $this->paths,
            'headers'                        => $this->headers,
            'translationConfig'              => $this->translationConfig,
            'configuration'                  => $this->configuration,
            'endpointsConfig'                => $this->endpointsConfig,
            'status'                         => $this->status,
            'logRetention'                   => $this->logRetention,
            'errorRetention'                 => $this->errorRetention,
            'objectCount'                    => $this->objectCount,
            'test'                           => $this->test,
            'rateLimitLimit'                 => $this->rateLimitLimit,
            'rateLimitRemaining'             => $this->rateLimitRemaining,
            'rateLimitReset'                 => $this->rateLimitReset,
            'rateLimitWindow'                => $this->rateLimitWindow,
            'lastCall'                       => isset($this->lastCall) ? $this->lastCall->format('c') : null,
            'lastSync'                       => isset($this->lastSync) ? $this->lastSync->format('c') : null,
            'dateCreated'                    => isset($this->dateCreated) ? $this->dateCreated->format('c') : null,
            'dateModified'                   => isset($this->dateModified) ? $this->dateModified->format('c') : null,
        ];

    }//end jsonSerialize()


}//end class
