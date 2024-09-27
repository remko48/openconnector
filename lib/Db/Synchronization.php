<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Synchronization extends Entity implements JsonSerializable
{
	protected ?string $entity = null;
	protected ?string $object = null;
	protected ?string $action = null;
	protected ?string $gateway = null;
	protected ?string $sourceObject = null;
	protected ?string $endpoint = null;
	protected ?string $sourceId = null;
	protected ?string $hash = null;
	protected ?string $sha = null;
	protected ?bool $blocked = null;
	protected ?DateTime $sourceLastChanged = null;
	protected ?DateTime $lastChecked = null;
	protected ?DateTime $lastSynced = null;
	protected ?DateTime $dateCreated = null;
	protected ?DateTime $dateModified = null;
	protected ?int $tryCounter = null;
	protected ?DateTime $dontSyncBefore = null;
	protected ?array $mapping = null;

	public function __construct() {
		$this->addType('entity', 'string');
		$this->addType('object', 'string');
		$this->addType('action', 'string');
		$this->addType('gateway', 'string');
		$this->addType('sourceObject', 'string');
		$this->addType('endpoint', 'string');
		$this->addType('sourceId', 'string');
		$this->addType('hash', 'string');
		$this->addType('sha', 'string');
		$this->addType('blocked', 'boolean');
		$this->addType('sourceLastChanged', 'datetime');
		$this->addType('lastChecked', 'datetime');
		$this->addType('lastSynced', 'datetime');
		$this->addType('dateCreated', 'datetime');
		$this->addType('dateModified', 'datetime');
		$this->addType('tryCounter', 'integer');
		$this->addType('dontSyncBefore', 'datetime');
		$this->addType('mapping', 'json');
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
			'entity' => $this->entity,
			'object' => $this->object,
			'action' => $this->action,
			'gateway' => $this->gateway,
			'sourceObject' => $this->sourceObject,
			'endpoint' => $this->endpoint,
			'sourceId' => $this->sourceId,
			'hash' => $this->hash,
			'sha' => $this->sha,
			'blocked' => $this->blocked,
			'sourceLastChanged' => $this->sourceLastChanged,
			'lastChecked' => $this->lastChecked,
			'lastSynced' => $this->lastSynced,
			'dateCreated' => isset($this->dateCreated) ? $this->dateCreated->format('c') : null,
			'dateModified' => isset($this->dateModified) ? $this->dateModified->format('c') : null,
			'tryCounter' => $this->tryCounter,
			'dontSyncBefore' => $this->dontSyncBefore,
			'mapping' => $this->mapping
		];
	}
}