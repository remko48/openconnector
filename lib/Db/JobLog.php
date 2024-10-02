<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class JobLog extends Entity implements JsonSerializable
{    
	protected string $level = 'INFO'; // log level
	protected string $message = 'success'; // log message
	protected ?string $jobId = null; // the id of the job in the job
	protected ?string $jobListId = null; // the id of the job in the job list
	protected ?string $jobClass = 'OCA\OpenConnector\Action\PingAction';
	protected ?array $arguments = null;
	protected ?int $executionTime = 3600; // the execution time in seconds
	protected ?string $userId = null; // the user which the job is running for security reasons
	protected ?DateTime $expires = null; // when the log will be cleared
	protected ?DateTime $lastRun = null; // the last time the job was run
	protected ?DateTime $nextRun = null; // the next time the job will be run
	protected ?DateTime $created = null; // the date and time the job was created	

    public function __construct() {
        $this->addType('level', 'string');
        $this->addType('message', 'string');
        $this->addType('jobId', 'string');
        $this->addType('jobListId', 'string');
        $this->addType('jobClass', 'string');
        $this->addType('arguments', 'json');
        $this->addType('executionTime', 'integer');
        $this->addType('userId', 'string');
        $this->addType('expires', 'datetime');
        $this->addType('lastRun', 'datetime');
        $this->addType('nextRun', 'datetime');
        $this->addType('created', 'datetime');
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
                // Handle or log the exception if needed
            }
        }

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'message' => $this->message,
            'jobId' => $this->jobId,
            'jobListId' => $this->jobListId,
            'jobClass' => $this->jobClass,
            'arguments' => $this->arguments,
            'executionTime' => $this->executionTime,
            'userId' => $this->userId,
            'expires' => $this->lastRun,
            'lastRun' => $this->lastRun,
            'nextRun' => $this->nextRun,
            'created' => $this->created,
        ];
    }
}