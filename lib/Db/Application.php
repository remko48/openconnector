<?php

declare(strict_types=1);

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @phpstan-type AppConfig array<string, mixed>
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
 * @method array|null getPermissions()
 * @method void setPermissions(?array $permissions)
 * @method string|null getClientId()
 * @method void setClientId(string $clientId)
 * @method string|null getClientSecret()
 * @method void setClientSecret(string $clientSecret)
 * @method array|null getWebhooks()
 * @method void setWebhooks(?array $webhooks)
 * @method bool|null getIsEnabled()
 * @method void setIsEnabled(bool $isEnabled)
 * @method DateTime|null getDateCreated()
 * @method void setDateCreated(DateTime $dateCreated)
 * @method DateTime|null getDateModified()
 * @method void setDateModified(DateTime $dateModified)
 * @method DateTime|null getLastAccess()
 * @method void setLastAccess(?DateTime $lastAccess)
 */
class Application extends Entity implements JsonSerializable
{
    /** @var string|null UUID van de applicatie */
    protected ?string $uuid = null;

    /** @var string|null Naam van de applicatie */
    protected ?string $name = null;

    /** @var string|null Beschrijving van de applicatie */
    protected ?string $description = null;

    /** @var string|null Type applicatie (bijv. 'api-client', 'webhook-subscriber', etc.) */
    protected ?string $type = null;

    /** @var array<string, mixed>|null Configuratie opties voor de applicatie */
    protected ?array $configuration = null;

    /** @var array<string, mixed>|null Permissies voor de applicatie */
    protected ?array $permissions = null;

    /** @var string|null Client ID voor authenticatie */
    protected ?string $clientId = null;

    /** @var string|null Client Secret voor authenticatie */
    protected ?string $clientSecret = null;

    /** @var array<string, mixed>|null Webhook configuraties */
    protected ?array $webhooks = null;

    /** @var bool|null Of de applicatie actief is */
    protected ?bool $isEnabled = true;

    /** @var DateTime|null Aanmaakdatum */
    protected ?DateTime $dateCreated = null;

    /** @var DateTime|null Laatste wijzigingsdatum */
    protected ?DateTime $dateModified = null;

    /** @var DateTime|null Laatste toegangsdatum */
    protected ?DateTime $lastAccess = null;

    /**
     * Constructor voor de Application entity
     */
    public function __construct() {
        $this->addType('uuid', 'string');
        $this->addType('name', 'string');
        $this->addType('description', 'string');
        $this->addType('type', 'string');
        $this->addType('configuration', 'json');
        $this->addType('permissions', 'json');
        $this->addType('clientId', 'string');
        $this->addType('clientSecret', 'string');
        $this->addType('webhooks', 'json');
        $this->addType('isEnabled', 'boolean');
        $this->addType('dateCreated', 'datetime');
        $this->addType('dateModified', 'datetime');
        $this->addType('lastAccess', 'datetime');
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
            'permissions' => $this->permissions,
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'webhooks' => $this->webhooks,
            'isEnabled' => $this->isEnabled,
            'dateCreated' => $this->dateCreated?->format('c'),
            'dateModified' => $this->dateModified?->format('c'),
            'lastAccess' => $this->lastAccess?->format('c'),
        ];
    }
} 