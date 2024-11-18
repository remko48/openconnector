<?php

declare(strict_types=1);

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @phpstan-type AuthConfig array<string, mixed>
 * 
 * @method string|null getUuid()
 * @method void setUuid(string $uuid)
 * @method string|null getName()
 * @method void setName(string $name)
 * @method string|null getDescription()
 * @method void setDescription(string $description)
 * @method string|null getType()
 * @method void setType(string $type)
 * @method array|null getConfiguration()
 * @method void setConfiguration(?array $configuration)
 * @method DateTime|null getDateCreated()
 * @method void setDateCreated(DateTime $dateCreated)
 * @method DateTime|null getDateModified()
 * @method void setDateModified(DateTime $dateModified)
 */
class Authentication extends Entity implements JsonSerializable
{
    /** @var string|null UUID van de authenticatie */
    protected ?string $uuid = null;

    /** @var string|null Naam van de authenticatie configuratie */
    protected ?string $name = null;

    /** @var string|null Beschrijving van de authenticatie configuratie */
    protected ?string $description = null;

    /** @var string|null Type authenticatie (bijv. 'apikey', 'jwt', etc.) */
    protected ?string $type = null;

    /** @var array<string, mixed>|null Configuratie opties voor het specifieke type */
    protected ?array $configuration = null;

    /** @var DateTime|null Aanmaakdatum */
    protected ?DateTime $dateCreated = null;

    /** @var DateTime|null Laatste wijzigingsdatum */
    protected ?DateTime $dateModified = null;

    /**
     * Constructor voor de Authentication entity
     */
    public function __construct() {
        $this->addType('uuid', 'string');
        $this->addType('name', 'string');
        $this->addType('description', 'string');
        $this->addType('type', 'string');
        $this->addType('configuration', 'json');
        $this->addType('dateCreated', 'datetime');
        $this->addType('dateModified', 'datetime');
    }

    /**
     * Serialiseert het object naar JSON
     * 
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'configuration' => $this->configuration,
            'dateCreated' => $this->dateCreated?->format('c'),
            'dateModified' => $this->dateModified?->format('c'),
        ];
    }
} 