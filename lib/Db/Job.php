<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Job extends Entity implements JsonSerializable
{
	protected ?string $name = null;
	protected ?string $description = null;
	protected ?string $reference = null;
	protected ?string $version = null;
	protected ?string $crontab = null;
	protected ?string $userId = null;
	protected ?string $throws = null;
	protected ?array $data = null;
	protected ?DateTime $lastRun = null;
	protected ?DateTime $nextRun = null;
	protected ?bool $isEnabled = null;
	protected ?DateTime $dateCreated = null;
	protected ?DateTime $dateModified = null;
	protected ?array $listens = null;
	protected ?array $conditions = null;
	protected ?string $class = null;
	protected ?int $priority = null;
	protected ?bool $async = null;
	protected ?array $configuration = null;
	protected ?bool $isLockable = null;
	protected ?bool $locked = null;
	protected ?int $lastRunTime = null;
	protected ?bool $status = null;
	protected ?array $actionHandlerConfiguration = null;

	public function __construct() {
		$this->addType('name', 'string');
		$this->addType('description', 'string');
		$this->addType('reference', 'string');
		$this->addType('version', 'string');
		$this->addType('crontab', 'string');
		$this->addType('userId', 'string');
		$this->addType('throws', 'string');
		$this->addType('data', 'json');
		$this->addType('lastRun', 'datetime');
		$this->addType('nextRun', 'datetime');
		$this->addType('isEnabled', 'boolean');
		$this->addType('dateCreated', 'datetime');
		$this->addType('dateModified', 'datetime');
		$this->addType('listens', 'json');
		$this->addType('conditions', 'json');
		$this->addType('class', 'string');
		$this->addType('priority', 'integer');
		$this->addType('async', 'boolean');
		$this->addType('configuration', 'json');
		$this->addType('isLockable', 'boolean');
		$this->addType('locked', 'boolean');
		$this->addType('lastRunTime', 'integer');
		$this->addType('status', 'boolean');
		$this->addType('actionHandlerConfiguration', 'json');
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
			'name' => $this->name,
			'description' => $this->description,
			'reference' => $this->reference,
			'version' => $this->version,
			'crontab' => $this->crontab,
			'userId' => $this->userId,
			'throws' => $this->throws,
			'data' => $this->data,
			'lastRun' => $this->lastRun,
			'nextRun' => $this->nextRun,
			'isEnabled' => $this->isEnabled,
			'dateCreated' => isset($this->dateCreated) ? $this->dateCreated->format('c') : null,
			'dateModified' => isset($this->dateModified) ? $this->dateModified->format('c') : null,
			'listens' => $this->listens,
			'conditions' => $this->conditions,
			'class' => $this->class,
			'priority' => $this->priority,
			'async' => $this->async,
			'configuration' => $this->configuration,
			'isLockable' => $this->isLockable,
			'locked' => $this->locked,
			'lastRunTime' => $this->lastRunTime,
			'status' => $this->status,
			'actionHandlerConfiguration' => $this->actionHandlerConfiguration
		];
	}
}