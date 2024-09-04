<?php

namespace OCA\OpenConnector\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Job extends Entity implements JsonSerializable {
    protected $name;
    protected $description;
    protected $reference;
    protected $version;
    protected $crontab;
    protected $userId;
    protected $throws;
    protected $data;
    protected $lastRun;
    protected $nextRun;
    protected $isEnabled;
    protected $dateCreated;
    protected $dateModified;
    protected $listens;
    protected $conditions;
    protected $class;
    protected $priority;
    protected $async;
    protected $configuration;
    protected $isLockable;
    protected $locked;
    protected $lastRunTime;
    protected $status;
    protected $actionHandlerConfiguration;

    public function __construct() {
        $this->addType('isEnabled', 'boolean');
        $this->addType('priority', 'integer');
        $this->addType('async', 'boolean');
        $this->addType('isLockable', 'boolean');
        $this->addType('lastRunTime', 'integer');
        $this->addType('status', 'boolean');
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'reference' => $this->reference,
            'version' => $this->version,
            'crontab' => $this->crontab,
            'userId' => $this->userId,
            'throws' => $this->throws,
            'data' => $this->data,
            'lastRun' => $this->lastRun,
            'nextRun' => $this->nextRun,
            'isEnabled' => $this->isEnabled,
            'dateCreated' => $this->dateCreated,
            'dateModified' => $this->dateModified,
            'listens' => $this->listens,
            'conditions' => $this->conditions,
            'class' => $this->class,
            'priority' => $this->priority,
            'async' => $this->async,
            'configuration' => $this->configuration,
            'isLockable' => $this->isLockable,
            'locked' => $this->locked,
            'lastRunTime' => $this->lastRunTime,
            'status' => $this->status,
            'actionHandlerConfiguration' => $this->actionHandlerConfiguration
        ];
    }
}