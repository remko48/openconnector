<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Endpoint extends Entity implements JsonSerializable
{
	protected ?string $reference = null;
	protected ?string $version = null;
	protected ?string $name = null;
	protected ?string $description = null;
	protected ?array $schemas = null;
	protected ?array $methods = null;
	protected ?DateTime $dateCreated = null;
	protected ?DateTime $dateModified = null;

	public function __construct() {
		$this->addType('reference', 'string');
		$this->addType('version', 'string');
		$this->addType('name', 'string');
		$this->addType('description', 'string');
		$this->addType('schemas', 'json');
		$this->addType('methods', 'json');
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

		foreach ($object as $key => $value) {
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
			'reference' => $this->reference,
			'version' => $this->version,
			'name' => $this->name,
			'description' => $this->description,
			'schemas' => $this->schemas,
			'methods' => $this->methods,
			'dateCreated' => $this->dateCreated,
			'dateModified' => $this->dateModified,
		];
	}
}
