<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Source extends Entity implements JsonSerializable
{
	protected ?string $name = null;
	protected ?string $description = null;
	protected ?string $reference = null;
	protected ?string $version = null;
	protected ?string $location = null;
	protected ?bool $isEnabled = null;
	protected ?string $type = null;
	protected ?string $authorizationHeader = null;
	protected ?string $auth = null;
	protected ?array $authenticationConfig = null;
	protected ?string $authorizationPassthroughMethod = null;
	protected ?string $locale = null;
	protected ?string $accept = null;
	protected ?string $jwt = null;
	protected ?string $jwtId = null;
	protected ?string $secret = null;
	protected ?string $username = null;
	protected ?string $password = null;
	protected ?string $apikey = null;
	protected ?string $documentation = null;
	protected ?array $loggingConfig = null;
	protected ?string $oas = null;
	protected ?array $paths = null;
	protected ?array $headers = null;
	protected ?array $translationConfig = null;
	protected ?array $configuration = null;
	protected ?array $endpointsConfig = null;
	protected ?string $status = null;
	protected ?DateTime $lastCall = null;
	protected ?DateTime $lastSync = null;
	protected ?int $objectCount = null;
	protected ?DateTime $dateCreated = null;
	protected ?DateTime $dateModified = null;
	protected ?bool $test = null;

	public function __construct() {
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
		$this->addType('lastCall', 'datetime');
		$this->addType('lastSync', 'datetime');
		$this->addType('objectCount', 'integer');
		$this->addType('dateCreated', 'datetime');
		$this->addType('dateModified', 'datetime');
		$this->addType('test', 'boolean');
	}

	public function getJsonFields(): array
	{
		return array_keys(
			array_filter($this->getFieldTypes(), function ($field) {
				return $field === 'json';
			})
		);
	}

	public function hydrate(array $object): self
	{
		$jsonFields = $this->getJsonFields();

		foreach($object as $key => $value) {
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
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'reference' => $this->reference,
			'version' => $this->version,
			'location' => $this->location,
			'isEnabled' => $this->isEnabled,
			'type' => $this->type,
			'authorizationHeader' => $this->authorizationHeader,
			'auth' => $this->auth,
			'authenticationConfig' => $this->authenticationConfig,
			'authorizationPassthroughMethod' => $this->authorizationPassthroughMethod,
			'locale' => $this->locale,
			'accept' => $this->accept,
			'jwt' => $this->jwt,
			'jwtId' => $this->jwtId,
			'secret' => $this->secret,
			'username' => $this->username,
			'password' => $this->password,
			'apikey' => $this->apikey,
			'documentation' => $this->documentation,
			'loggingConfig' => $this->loggingConfig,
			'oas' => $this->oas,
			'paths' => $this->paths,
			'headers' => $this->headers,
			'translationConfig' => $this->translationConfig,
			'configuration' => $this->configuration,
			'endpointsConfig' => $this->endpointsConfig,
			'status' => $this->status,
			'lastCall' => $this->lastCall,
			'lastSync' => $this->lastSync,
			'objectCount' => $this->objectCount,
			'dateCreated' => $this->dateCreated,
			'dateModified' => $this->dateModified,
			'test' => $this->test
		];
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(?string $name): void
	{
		$this->name = $name;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(?string $description): void
	{
		$this->description = $description;
	}

	public function getReference(): ?string
	{
		return $this->reference;
	}

	public function setReference(?string $reference): void
	{
		$this->reference = $reference;
	}

	public function getVersion(): ?string
	{
		return $this->version;
	}

	public function setVersion(?string $version): void
	{
		$this->version = $version;
	}

	public function getLocation(): ?string
	{
		return $this->location;
	}

	public function setLocation(?string $location): void
	{
		$this->location = $location;
	}

	public function getIsEnabled(): ?bool
	{
		return $this->isEnabled;
	}

	public function setIsEnabled(?bool $isEnabled): void
	{
		$this->isEnabled = $isEnabled;
	}

	public function getType(): ?string
	{
		return $this->type;
	}

	public function setType(?string $type): void
	{
		$this->type = $type;
	}

	public function getAuthorizationHeader(): ?string
	{
		return $this->authorizationHeader;
	}

	public function setAuthorizationHeader(?string $authorizationHeader): void
	{
		$this->authorizationHeader = $authorizationHeader;
	}

	public function getAuth(): ?string
	{
		return $this->auth;
	}

	public function setAuth(?string $auth): void
	{
		$this->auth = $auth;
	}

	public function getAuthenticationConfig(): ?array
	{
		return $this->authenticationConfig;
	}

	public function setAuthenticationConfig(?array $authenticationConfig): void
	{
		$this->authenticationConfig = $authenticationConfig;
	}

	public function getAuthorizationPassthroughMethod(): ?string
	{
		return $this->authorizationPassthroughMethod;
	}

	public function setAuthorizationPassthroughMethod(?string $authorizationPassthroughMethod): void
	{
		$this->authorizationPassthroughMethod = $authorizationPassthroughMethod;
	}

	public function getLocale(): ?string
	{
		return $this->locale;
	}

	public function setLocale(?string $locale): void
	{
		$this->locale = $locale;
	}

	public function getAccept(): ?string
	{
		return $this->accept;
	}

	public function setAccept(?string $accept): void
	{
		$this->accept = $accept;
	}

	public function getJwt(): ?string
	{
		return $this->jwt;
	}

	public function setJwt(?string $jwt): void
	{
		$this->jwt = $jwt;
	}

	public function getJwtId(): ?string
	{
		return $this->jwtId;
	}

	public function setJwtId(?string $jwtId): void
	{
		$this->jwtId = $jwtId;
	}

	public function getSecret(): ?string
	{
		return $this->secret;
	}

	public function setSecret(?string $secret): void
	{
		$this->secret = $secret;
	}

	public function getUsername(): ?string
	{
		return $this->username;
	}

	public function setUsername(?string $username): void
	{
		$this->username = $username;
	}

	public function getPassword(): ?string
	{
		return $this->password;
	}

	public function setPassword(?string $password): void
	{
		$this->password = $password;
	}

	public function getApikey(): ?string
	{
		return $this->apikey;
	}

	public function setApikey(?string $apikey): void
	{
		$this->apikey = $apikey;
	}

	public function getDocumentation(): ?string
	{
		return $this->documentation;
	}

	public function setDocumentation(?string $documentation): void
	{
		$this->documentation = $documentation;
	}

	public function getLoggingConfig(): ?array
	{
		return $this->loggingConfig;
	}

	public function setLoggingConfig(?array $loggingConfig): void
	{
		$this->loggingConfig = $loggingConfig;
	}

	public function getOas(): ?string
	{
		return $this->oas;
	}

	public function setOas(?string $oas): void
	{
		$this->oas = $oas;
	}

	public function getPaths(): ?array
	{
		return $this->paths;
	}

	public function setPaths(?array $paths): void
	{
		$this->paths = $paths;
	}

	public function getHeaders(): ?array
	{
		return $this->headers;
	}

	public function setHeaders(?array $headers): void
	{
		$this->headers = $headers;
	}

	public function getTranslationConfig(): ?array
	{
		return $this->translationConfig;
	}

	public function setTranslationConfig(?array $translationConfig): void
	{
		$this->translationConfig = $translationConfig;
	}

	public function getConfiguration(): ?array
	{
		return $this->configuration;
	}

	public function setConfiguration(?array $configuration): void
	{
		$this->configuration = $configuration;
	}

	public function getEndpointsConfig(): ?array
	{
		return $this->endpointsConfig;
	}

	public function setEndpointsConfig(?array $endpointsConfig): void
	{
		$this->endpointsConfig = $endpointsConfig;
	}

	public function getStatus(): ?string
	{
		return $this->status;
	}

	public function setStatus(?string $status): void
	{
		$this->status = $status;
	}

	public function getLastCall(): ?DateTime
	{
		return $this->lastCall;
	}

	public function setLastCall(?DateTime $lastCall): void
	{
		$this->lastCall = $lastCall;
	}

	public function getLastSync(): ?DateTime
	{
		return $this->lastSync;
	}

	public function setLastSync(?DateTime $lastSync): void
	{
		$this->lastSync = $lastSync;
	}

	public function getObjectCount(): ?int
	{
		return $this->objectCount;
	}

	public function setObjectCount(?int $objectCount): void
	{
		$this->objectCount = $objectCount;
	}

	public function getDateCreated(): ?DateTime
	{
		return $this->dateCreated;
	}

	public function setDateCreated(?DateTime $dateCreated): void
	{
		$this->dateCreated = $dateCreated;
	}

	public function getDateModified(): ?DateTime
	{
		return $this->dateModified;
	}

	public function setDateModified(?DateTime $dateModified): void
	{
		$this->dateModified = $dateModified;
	}

	public function getTest(): ?bool
	{
		return $this->test;
	}

	public function setTest(?bool $test): void
	{
		$this->test = $test;
	}
}