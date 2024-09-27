<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Log extends Entity implements JsonSerializable
{
	protected ?string $type = null;
	protected ?string $callId = null;
	protected ?string $requestMethod = null;
	protected ?array $requestHeaders = null;
	protected ?array $requestQuery = null;
	protected ?string $requestPathInfo = null;
	protected ?array $requestLanguages = null;
	protected ?array $requestServer = null;
	protected ?string $requestContent = null;
	protected ?string $responseStatus = null;
	protected ?int $responseStatusCode = null;
	protected ?array $responseHeaders = null;
	protected ?string $responseContent = null;
	protected ?string $userId = null;
	protected ?string $session = null;
	protected ?array $sessionValues = null;
	protected ?int $responseTime = null;
	protected ?string $routeName = null;
	protected ?array $routeParameters = null;
	protected ?string $entity = null;
	protected ?string $endpoint = null;
	protected ?string $gateway = null;
	protected ?string $handler = null;
	protected ?string $objectId = null;
	protected ?DateTime $dateCreated = null;
	protected ?DateTime $dateModified = null;

	public function __construct() {
		$this->addType('type', 'string');
		$this->addType('callId', 'string');
		$this->addType('requestMethod', 'string');
		$this->addType('requestHeaders', 'json');
		$this->addType('requestQuery', 'json');
		$this->addType('requestPathInfo', 'string');
		$this->addType('requestLanguages', 'json');
		$this->addType('requestServer', 'json');
		$this->addType('requestContent', 'string');
		$this->addType('responseStatus', 'string');
		$this->addType('responseStatusCode', 'integer');
		$this->addType('responseHeaders', 'json');
		$this->addType('responseContent', 'string');
		$this->addType('userId', 'string');
		$this->addType('session', 'string');
		$this->addType('sessionValues', 'json');
		$this->addType('responseTime', 'integer');
		$this->addType('routeName', 'string');
		$this->addType('routeParameters', 'json');
		$this->addType('entity', 'string');
		$this->addType('endpoint', 'string');
		$this->addType('gateway', 'string');
		$this->addType('handler', 'string');
		$this->addType('objectId', 'string');
		$this->addType('dateCreated', 'datetime');
		$this->addType('dateModified', 'datetime');
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
//				("Error writing $key");
			}
		}

		return $this;
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->id,
			'type' => $this->type,
			'callId' => $this->callId,
			'requestMethod' => $this->requestMethod,
			'requestHeaders' => $this->requestHeaders,
			'requestQuery' => $this->requestQuery,
			'requestPathInfo' => $this->requestPathInfo,
			'requestLanguages' => $this->requestLanguages,
			'requestServer' => $this->requestServer,
			'requestContent' => $this->requestContent,
			'responseStatus' => $this->responseStatus,
			'responseStatusCode' => $this->responseStatusCode,
			'responseHeaders' => $this->responseHeaders,
			'responseContent' => $this->responseContent,
			'userId' => $this->userId,
			'session' => $this->session,
			'sessionValues' => $this->sessionValues,
			'responseTime' => $this->responseTime,
			'routeName' => $this->routeName,
			'routeParameters' => $this->routeParameters,
			'entity' => $this->entity,
			'endpoint' => $this->endpoint,
			'gateway' => $this->gateway,
			'handler' => $this->handler,
			'objectId' => $this->objectId,
			'dateCreated' => isset($this->dateCreated) ? $this->dateCreated->format('c') : null,
			'dateModified' => isset($this->dateModified) ? $this->dateModified->format('c') : null,
		];
	}
}