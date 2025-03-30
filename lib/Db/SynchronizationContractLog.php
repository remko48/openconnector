<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * Class SynchronizationContractLog
 *
 * Entity class representing a synchronization contract log entry
 */
class SynchronizationContractLog extends Entity implements JsonSerializable
{
    /**
     * The unique identifier of the synchronization contract log entry.
     *
     * @var string|null
     */
    protected ?string $uuid = null;

    /**
     * The message associated with the synchronization contract log entry.
     *
     * @var string|null
     */
    protected ?string $message = null;

    /**
     * The ID of the synchronization associated with this log entry.
     *
     * @var string|null
     */
    protected ?string $synchronizationId = null;

    /**
     * The ID of the synchronization contract associated with this log entry.
     *
     * @var string|null
     */
    protected ?string $synchronizationContractId = null;

    /**
     * The ID of the synchronization log associated with this log entry.
     *
     * @var string|null
     */
    protected ?string $synchronizationLogId = null;

    /**
     * The source data of the synchronization.
     *
     * @var array|null
     */
    protected ?array $source = [];

    /**
     * The target data of the synchronization.
     *
     * @var array|null
     */
    protected ?array $target = [];

    /**
     * The result data of the target.
     *
     * @var string|null
     */
    protected ?string $targetResult = null;

    /**
     * The ID of the user who initiated the synchronization.
     *
     * @var string|null
     */
    protected ?string $userId = null;

    /**
     * The session ID associated with the synchronization.
     *
     * @var string|null
     */
    protected ?string $sessionId = null;

    /**
     * Indicates if the synchronization was a test run.
     *
     * @var bool|null
     */
    protected ?bool $test = false;

    /**
     * Indicates if the synchronization was forced.
     *
     * @var bool|null
     */
    protected ?bool $force = false;

    /**
     * The expiration date and time of the synchronization contract log entry.
     *
     * @var DateTime|null
     */
    protected ?DateTime $expires = null;

    /**
     * The date and time the synchronization contract log entry was created.
     *
     * @var DateTime|null
     */
    protected ?DateTime $created = null;

    /**
     * Get the source data
     *
     * @return array The source data or null
     */
    public function getSource(): ?array
    {
        return $this->source;

    }//end getSource()


    /**
     * Get the target data
     *
     * @return array The target data or null
     */
    public function getTarget(): ?array
    {
        return $this->target;

    }//end getTarget()


    public function __construct()
    {
        $this->addType('uuid', 'string');
        $this->addType('message', 'string');
        $this->addType('synchronizationId', 'string');
        $this->addType('synchronizationContractId', 'string');
        $this->addType('synchronizationLogId', 'string');
        $this->addType('source', 'json');
        $this->addType('target', 'json');
        $this->addType('targetResult', 'string');
        $this->addType('userId', 'string');
        $this->addType('sessionId', 'string');
        $this->addType('test', 'boolean');
        $this->addType('force', 'boolean');
        $this->addType('expires', 'datetime');
        $this->addType('created', 'datetime');

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
                // Handle or log the exception if needed
            }
        }

        return $this;

    }//end hydrate()


    public function jsonSerialize(): array
    {
        return [
            'id'                        => $this->id,
            'uuid'                      => $this->uuid,
            'message'                   => $this->message,
            'synchronizationId'         => $this->synchronizationId,
            'synchronizationContractId' => $this->synchronizationContractId,
            'synchronizationLogId'      => $this->synchronizationLogId,
            'source'                    => $this->source,
            'target'                    => $this->target,
            'targetResult'              => $this->targetResult,
            'userId'                    => $this->userId,
            'sessionId'                 => $this->sessionId,
            'test'                      => $this->test,
            'force'                     => $this->force,
            'expires'                   => isset($this->expires) ? $this->expires->format('c') : null,
            'created'                   => isset($this->created) ? $this->created->format('c') : null,
        ];

    }//end jsonSerialize()


}//end class
