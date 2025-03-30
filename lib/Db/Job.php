<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Job extends Entity implements JsonSerializable
{

    // The unique identifier of the job
    protected ?string $uuid = null;

    // The name of the job
    protected ?string $name = null;

    // The description of the job
    protected ?string $description = null;

    // The reference of the job
    protected ?string $reference = null;

    // The version of the job
    protected ?string $version = '0.0.0';

    // The class responsible for executing the job
    protected ?string $jobClass = 'OCA\OpenConnector\Action\PingAction';

    // The arguments to be passed to the job
    protected ?array $arguments = null;

    // The interval in seconds between job executions
    protected ?int $interval = 3600;

    // The maximum execution time in seconds
    protected ?int $executionTime = 3600;

    // Indicates if the job is time-sensitive and should be executed even under heavy load
    protected ?bool $timeSensitive = true;

    // Indicates if the job can be executed in parallel
    protected ?bool $allowParallelRuns = false;

    // Indicates if the job is enabled
    protected ?bool $isEnabled = true;

    // Indicates if the job will only run once and then disable itself
    protected ?bool $singleRun = false;

    // The date and time after which the job should be executed
    protected ?DateTime $scheduleAfter = null;

    // The user for whom the job is running, for security reasons
    protected ?string $userId = null;

    // The ID of the job in the job list
    protected ?string $jobListId = null;

    // The duration in seconds to retain all logs
    protected ?int $logRetention = 3600;

    // The duration in seconds to retain error logs
    protected ?int $errorRetention = 86400;

    // The last time the job was run
    protected ?DateTime $lastRun = null;

    // The next scheduled time for the job to run
    protected ?DateTime $nextRun = null;

    // The date and time the job was created
    protected ?DateTime $created = null;

    // The date and time the job was last updated
    protected ?DateTime $updated = null;


    /**
     * Get the job arguments
     *
     * @return array The job arguments or empty array if null
     */
    public function getArguments(): array
    {
        return ($this->arguments ?? []);

    }//end getArguments()


    public function __construct()
    {
        $this->addType('uuid', 'string');
        $this->addType('name', 'string');
        $this->addType('description', 'string');
        $this->addType(fieldName:'reference', type: 'string');
        $this->addType('version', 'string');
        $this->addType('jobClass', 'string');
        $this->addType('arguments', 'json');
        $this->addType('interval', 'integer');
        $this->addType('executionTime', 'integer');
        $this->addType('timeSensitive', 'boolean');
        $this->addType('allowParallelRuns', 'boolean');
        $this->addType('isEnabled', 'boolean');
        $this->addType('singleRun', 'boolean');
        $this->addType('scheduleAfter', 'datetime');
        $this->addType('userId', 'string');
        $this->addType('jobListId', 'string');
        $this->addType('logRetention', 'integer');
        $this->addType('errorRetention', 'integer');
        $this->addType('lastRun', 'datetime');
        $this->addType('nextRun', 'datetime');
        $this->addType('created', 'datetime');
        $this->addType('updated', 'datetime');

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
                // ("Error writing $key");
            }
        }

        return $this;

    }//end hydrate()


    public function jsonSerialize(): array
    {
        return [
            'id'                => $this->id,
            'uuid'              => $this->uuid,
            'name'              => $this->name,
            'description'       => $this->description,
            'reference'         => $this->reference,
            'version'           => $this->version,
            'jobClass'          => $this->jobClass,
            'arguments'         => $this->arguments,
            'interval'          => $this->interval,
            'executionTime'     => $this->executionTime,
            'timeSensitive'     => $this->timeSensitive,
            'allowParallelRuns' => $this->allowParallelRuns,
            'isEnabled'         => $this->isEnabled,
            'singleRun'         => $this->singleRun,
            'scheduleAfter'     => isset($this->scheduleAfter) ? $this->scheduleAfter->format('c') : $this->scheduleAfter,
            'userId'            => $this->userId,
            'jobListId'         => $this->jobListId,
            'logRetention'      => $this->logRetention,
            'errorRetention'    => $this->errorRetention,
            'lastRun'           => isset($this->lastRun) ? $this->lastRun->format('c') : null,
            'nextRun'           => isset($this->nextRun) ? $this->nextRun->format('c') : null,
            'created'           => isset($this->created) ? $this->created->format('c') : null,
            'updated'           => isset($this->updated) ? $this->updated->format('c') : null,
        ];

    }//end jsonSerialize()


}//end class
