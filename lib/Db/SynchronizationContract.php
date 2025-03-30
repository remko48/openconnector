<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * This class is used to define a contract for a synchronization. Or in other words, a contract between a source and target object.
 *
 * @package OCA\OpenConnector\Db
 */
class SynchronizationContract extends Entity implements JsonSerializable
{


    /**
     * @var string|null The ID of the object in the source.
     * @todo can be removed when migrations are merged
     */
    protected ?string $sourceId = null;

    /**
     * @var string|null The hash of the object in the source.
     * @todo can be removed when migrations are merged
     */
    protected ?string $sourceHash = null;

    /**
     * @var string|null The unique identifier of the synchronization contract.
     */
    protected ?string $uuid = null;

    /**
     * @var string|null The version of the synchronization.
     */
    protected ?string $version = null;

    /**
     * @var string|null The ID of the synchronization that this contract belongs to.
     */
    protected ?string $synchronizationId = null;

    // Source

    /**
     * @var string|null The ID of the object in the source.
     */
    protected ?string $originId = null;

    /**
     * @var string|null The hash of the object in the source.
     */
    protected ?string $originHash = null;

    /**
     * @var DateTime|null The last changed date of the object in the source.
     */
    protected ?DateTime $sourceLastChanged = null;

    /**
     * @var DateTime|null The last checked date of the object in the source.
     */
    protected ?DateTime $sourceLastChecked = null;

    /**
     * @var DateTime|null The last synced date of the object in the source.
     */
    protected ?DateTime $sourceLastSynced = null;

    // Target

    /**
     * @var string|null The ID of the object in the target.
     */
    protected ?string $targetId = null;

    /**
     * @var string|null The hash of the object in the target.
     */
    protected ?string $targetHash = null;

    /**
     * @var DateTime|null The last changed date of the object in the target.
     */
    protected ?DateTime $targetLastChanged = null;

    /**
     * @var DateTime|null The last checked date of the object in the target.
     */
    protected ?DateTime $targetLastChecked = null;

    /**
     * @var DateTime|null The last synced date of the object in the target.
     */
    protected ?DateTime $targetLastSynced = null;

    /**
     * @var string|null The last CRUD action performed on the target (create, read, update, delete).
     */
    protected ?string $targetLastAction = null;

    // General

    /**
     * @var DateTime|null The date and time the synchronization was created.
     */
    protected ?DateTime $created = null;

    /**
     * @var DateTime|null The date and time the synchronization was last updated.
     */
    protected ?DateTime $updated = null;
    
    public function __construct()
    {
        $this->addType('uuid', 'string');
        $this->addType('version', 'string');
        $this->addType('synchronizationId', 'string');
        $this->addType('originId', 'string');
        $this->addType('originHash', 'string');
        $this->addType('sourceLastChanged', 'datetime');
        $this->addType('sourceLastChecked', 'datetime');
        $this->addType('sourceLastSynced', 'datetime');
        $this->addType('targetId', 'string');
        $this->addType('targetHash', 'string');
        $this->addType('targetLastChanged', 'datetime');
        $this->addType('targetLastChecked', 'datetime');
        $this->addType('targetLastSynced', 'datetime');
        $this->addType('targetLastAction', 'string');
        $this->addType('created', 'datetime');
        $this->addType('updated', 'datetime');

        // @todo can be removed when migrations are merged
        $this->addType('sourceId', 'string');
        $this->addType('sourceHash', 'string');

    }//end __construct()


    public function getJsonFields(): array
    {
        return array_keys(
            array_filter(
                $this->getFieldTypes(),
                function ($field) {
                        return $field === 'json';
                }
            )
        );

    }//end getJsonFields()


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
                // Error handling could be improved here
            }
        }

        return $this;

    }//end hydrate()


    public function jsonSerialize(): array
    {
        return [
            'id'                => $this->id,
            'uuid'              => $this->uuid,
            'version'           => $this->version,
            'synchronizationId' => $this->synchronizationId,
            'originId'          => $this->originId,
            'originHash'        => $this->originHash,
            'sourceLastChanged' => isset($this->sourceLastChanged) ? $this->sourceLastChanged->format('c') : null,
            'sourceLastChecked' => isset($this->sourceLastChecked) ? $this->sourceLastChecked->format('c') : null,
            'sourceLastSynced'  => isset($this->sourceLastSynced) ? $this->sourceLastSynced->format('c') : null,
            'targetId'          => $this->targetId,
            'targetHash'        => $this->targetHash,
            'targetLastChanged' => isset($this->targetLastChanged) ? $this->targetLastChanged->format('c') : null,
            'targetLastChecked' => isset($this->targetLastChecked) ? $this->targetLastChecked->format('c') : null,
            'targetLastSynced'  => isset($this->targetLastSynced) ? $this->targetLastSynced->format('c') : null,
            'targetLastAction'  => $this->targetLastAction,
            'created'           => isset($this->created) ? $this->created->format('c') : null,
            'updated'           => isset($this->updated) ? $this->updated->format('c') : null,
        // @todo these 2 can be removed when migrations are merged
            'sourceId'          => $this->sourceId,
            'sourceHash'        => $this->sourceHash,
        ];

    }//end jsonSerialize()


}//end class
