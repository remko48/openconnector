<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class CallLog extends Entity implements JsonSerializable
{    
    protected ?int $statusCode = null;
    protected ?string $statusMessage = null;
    protected ?array $request = null;
    protected ?array $response = null;
    protected ?int $sourceId = null;
    protected ?int $actionId = null;
    protected ?int $synchronizationId = null;
    protected ?DateTime $createdAt = null;
    protected ?DateTime $updatedAt = null;

    public function __construct() {
        $this->addType('statusCode', 'integer');
        $this->addType('statusMessage', 'string');
        $this->addType('request', 'json');
        $this->addType('response', 'json');
        $this->addType('sourceId', 'integer');
        $this->addType('actionId', 'integer');
        $this->addType('synchronizationId', 'integer');
        $this->addType('createdAt', 'datetime');
        $this->addType('updatedAt', 'datetime');
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
            'statusCode' => $this->statusCode,
            'statusMessage' => $this->statusMessage,
            'request' => $this->request,
            'response' => $this->response,
            'sourceId' => $this->sourceId,
            'actionId' => $this->actionId,
            'synchronizationId' => $this->synchronizationId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}