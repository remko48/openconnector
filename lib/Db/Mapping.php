<?php

namespace OCA\OpenConnector\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Mapping extends Entity implements JsonSerializable {
    protected $reference;
    protected $version;
    protected $name;
    protected $description;
    protected $mapping;
    protected $unset;
    protected $cast;
    protected $passTrough;
    protected $dateCreated;
    protected $dateModified;

    public function __construct() {
        $this->addType('passTrough', 'boolean');
    }

    public function jsonSerialize() {
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