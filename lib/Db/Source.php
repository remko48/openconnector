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
}