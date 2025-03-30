<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class JobLog extends Entity implements JsonSerializable
{
    /**
     * The unique identifier of the job log entry.
     *
     * @var string|null
     */
    protected ?string $uuid = null;

    /**
     * The log level of the job log entry.
     *
     * @var string
     */
    protected string $level = 'INFO';

    /**
     * The message associated with the job log entry.
     *
     * @var string
     */
    protected string $message = 'success';

    /**
     * The ID of the job associated with this log entry.
     *
     * @var string|null
     */
    protected ?string $jobId = null;

    /**
     * The ID of the job list associated with this log entry.
     *
     * @var string|null
     */
    protected ?string $jobListId = null;

    /**
     * The class of the job associated with this log entry.
     *
     * @var string|null
     */
    protected ?string $jobClass = 'OCA\OpenConnector\Action\PingAction';

    /**
     * The arguments of the job.
     *
     * @var array|null
     */
    protected ?array $arguments = [];

    /**
     * The execution time of the job in seconds.
     *
     * @var int|null
     */
    protected ?int $executionTime = 3600;

    /**
     * The ID of the user who initiated the job.
     *
     * @var string|null
     */
    protected ?string $userId = null;

    /**
     * The session ID associated with the job.
     *
     * @var string|null
     */
    protected ?string $sessionId = null;

    /**
     * The stack trace of the job.
     *
     * @var array|null
     */
    protected ?array $stackTrace = [];

    /**
     * The expiration date and time of the job log entry.
     *
     * @var DateTime|null
     */
    protected ?DateTime $expires = null;

    /**
     * The last run date and time of the job.
     *
     * @var DateTime|null
     */
    protected ?DateTime $lastRun = null;

    /**
     * The next run date and time of the job.
     *
     * @var DateTime|null
     */
    protected ?DateTime $nextRun = null;

    /**
     * The creation date and time of the job log entry.
     *
     * @var DateTime|null
     */
    protected ?DateTime $created = null;


    /**
     * Get the job arguments
     *
     * @return array The job arguments or empty array if null
     */
    public function getArguments(): array
    {
        return ($this->arguments ?? []);

    }//end getArguments()


    /**
     * Get the stack trace
     *
     * @return array The stack trace or empty array if null
     */
    public function getStackTrace(): array
    {
        return ($this->stackTrace ?? []);

    }//end getStackTrace()


    public function __construct()
    {
        $this->addType('uuid', 'string');
        $this->addType('level', 'string');
        $this->addType('message', 'string');
        $this->addType('jobId', 'string');
        $this->addType('jobListId', 'string');
        $this->addType('jobClass', 'string');
        $this->addType('arguments', 'json');
        $this->addType('executionTime', 'integer');
        $this->addType('userId', 'string');
        $this->addType('sessionId', 'string');
        $this->addType('stackTrace', 'json');
        $this->addType('expires', 'datetime');
        $this->addType('lastRun', 'datetime');
        $this->addType('nextRun', 'datetime');
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
            'id'            => $this->id,
            'uuid'          => $this->uuid,
            'level'         => $this->level,
            'message'       => $this->message,
            'jobId'         => $this->jobId,
            'jobListId'     => $this->jobListId,
            'jobClass'      => $this->jobClass,
            'arguments'     => $this->arguments,
            'executionTime' => $this->executionTime,
            'userId'        => $this->userId,
            'sessionId'     => $this->sessionId,
            'stackTrace'    => $this->stackTrace,
            'expires'       => isset($this->expires) ? $this->expires->format('c') : null,
            'lastRun'       => isset($this->lastRun) ? $this->lastRun->format('c') : null,
            'nextRun'       => isset($this->nextRun) ? $this->nextRun->format('c') : null,
            'created'       => isset($this->created) ? $this->created->format('c') : null,

        ];

    }//end jsonSerialize()


}//end class
