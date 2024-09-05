<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Mapping extends Entity implements JsonSerializable
{
	protected ?string $reference = null;
	protected ?string $version = null;
	protected ?string $name = null;
	protected ?string $description = null;
	protected ?array $mapping = null;
	protected ?array $unset = null;
	protected ?array $cast = null;
	protected ?bool $passTrough = null;
	protected ?DateTime $dateCreated = null;
	protected ?DateTime $dateModified = null;

	public function __construct() {
		$this->addType('reference', 'string');
		$this->addType('version', 'string');
		$this->addType('name', 'string');
		$this->addType('description', 'string');
		$this->addType('mapping', 'json');
		$this->addType('unset', 'json');
		$this->addType('cast', 'json');
		$this->addType('passTrough', 'boolean');
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
			'reference' => $this->reference,
			'version' => $this->version,
			'name' => $this->name,
			'description' => $this->description,
			'mapping' => $this->mapping,
			'unset' => $this->unset,
			'cast' => $this->cast,
			'passTrough' => $this->passTrough,
			'dateCreated' => $this->dateCreated,
			'dateModified' => $this->dateModified
		];
	}
}