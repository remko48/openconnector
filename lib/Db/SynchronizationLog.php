<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * Entity class representing a synchronization log entry
 */
class SynchronizationLog extends Entity implements JsonSerializable
{
    /**
     * The unique identifier of the synchronization log entry.
     *
     * @var string|null
     */
    protected ?string $uuid = null;

    /**
     * The message associated with the synchronization log entry.
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
     * The result data of the synchronization.
     *
     * @var array|null
     */
    protected ?array $result = [];

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
     * @var bool
     */
    protected bool $test = false;

    /**
     * Indicates if the synchronization was forced.
     *
     * @var bool
     */
    protected bool $force = false;

    /**
     * The execution time of the synchronization in seconds.
     *
     * @var int
     */
    protected int $executionTime = 0;

    /**
     * The date and time the synchronization log entry was created.
     *
     * @var DateTime|null
     */
    protected ?DateTime $created = null;

    /**
     * The date and time the synchronization log entry expires.
     *
     * @var DateTime|null
     */
    protected ?DateTime $expires = null;


    /**
     * Get the synchronization result
     *
     * @return array The result data or empty array if null
     */
    public function getResult(): array
    {
        return ($this->result ?? []);

    }//end getResult()


    public function __construct()
    {
        $this->addType('uuid', 'string');
        $this->addType('message', 'string');
        $this->addType('synchronizationId', 'string');
        $this->addType('result', 'json');
        $this->addType('userId', 'string');
        $this->addType('sessionId', 'string');
        $this->addType('test', 'boolean');
        $this->addType('force', 'boolean');
        $this->addType('executionTime', 'integer');
        $this->addType('created', 'datetime');
        $this->addType('expires', 'datetime');

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
            'id'                => $this->id,
            'uuid'              => $this->uuid,
            'message'           => $this->message,
            'synchronizationId' => $this->synchronizationId,
            'result'            => $this->result,
            'userId'            => $this->userId,
            'sessionId'         => $this->sessionId,
            'test'              => $this->test,
            'force'             => $this->force,
            'executionTime'     => $this->executionTime,
            'created'           => isset($this->created) ? $this->created->format('c') : null,
            'expires'           => isset($this->expires) ? $this->expires->format('c') : null,
        ];

    }//end jsonSerialize()


}//end class
