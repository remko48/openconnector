<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * Class Rule
 * 
 * Represents a rule that can be triggered during endpoint handling
 *
 * @package OCA\OpenConnector\Db
 */
class Rule extends Entity implements JsonSerializable
{
    protected ?string $uuid = null;
    protected ?string $name = null;
    protected ?string $description = null;
    protected ?string $action = null; // create, read, update, delete
    protected ?string $timing = 'before'; // before or after
    protected ?array $conditions = []; // JSON Logic format conditions
    protected ?string $type = null; // mapping, error, script, synchronization
    protected ?array $configuration = []; // Type-specific configuration
    protected int $order = 0; // Order in which the rule should be applied
    protected ?DateTime $created = null;
    protected ?DateTime $updated = null;

    public function __construct() {
        $this->addType('uuid', 'string');
        $this->addType('name', 'string');
        $this->addType('description', 'string');
        $this->addType('action', 'string');
        $this->addType('timing', 'string');
        $this->addType('conditions', 'json');
        $this->addType('type', 'string');
        $this->addType('configuration', 'json');
        $this->addType('order', 'integer');
        $this->addType('created', 'datetime');
        $this->addType('updated', 'datetime');
    }

    // ... existing methods ...

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'action' => $this->action,
            'timing' => $this->timing,
            'conditions' => $this->conditions,
            'type' => $this->type,
            'configuration' => $this->configuration,
            'order' => $this->order,
            'created' => isset($this->created) ? $this->created->format('c') : null,
            'updated' => isset($this->updated) ? $this->updated->format('c') : null
        ];
    }
} 