<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Job extends Entity implements JsonSerializable
{
	protected ?string $name = null;
	protected ?string $description = null;
	protected ?integer $interval = null;
	protected ?bool $timeSensitive = true;
	protected ?bool $allowParallelRuns = false;
	protected ?bool $isEnabled = true;
	protected ?string $userId = null;
	protected ?array $data = null;
	protected ?DateTime $created = null;
	protected ?DateTime $updated = null;

	public function __construct() {
		$this->addType('name', 'string');
		$this->addType('description', 'string');
		$this->addType('interval', 'integer');
		$this->addType('timeSensitive', 'boolean');
		$this->addType('allowParallelRuns', 'boolean');
		$this->addType('isEnabled', 'boolean');
		$this->addType('userId', 'string');
		$this->addType('data', 'json');
		$this->addType('created', 'datetime');
		$this->addType('updated', 'datetime');
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
			'interval' => $this->interval,
			'timeSensitive' => $this->timeSensitive,
			'allowParallelRuns' => $this->allowParallelRuns,
			'isEnabled' => $this->isEnabled,
			'userId' => $this->userId,
			'data' => $this->data,
			'created' => $this->created,
			'updated' => $this->updated,
		];
	}
}