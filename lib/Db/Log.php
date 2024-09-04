<?php

namespace OCA\OpenConnector\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Log extends Entity implements JsonSerializable {
    protected $type;
    protected $callId;
    protected $requestMethod;
    protected $requestHeaders;
    protected $requestQuery;
    protected $requestPathInfo;
    protected $requestLanguages;
    protected $requestServer;
    protected $requestContent;
    protected $responseStatus;
    protected $responseStatusCode;
    protected $responseHeaders;
    protected $responseContent;
    protected $userId;
    protected $session;
    protected $sessionValues;
    protected $responseTime;
    protected $routeName;
    protected $routeParameters;
    protected $entity;
    protected $endpoint;
    protected $gateway;
    protected $handler;
    protected $objectId;
    protected $dateCreated;
    protected $dateModified;

    public function __construct() {
        $this->addType('responseStatusCode', 'integer');
        $this->addType('responseTime', 'integer');
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'callId' => $this->callId,
            'requestMethod' => $this->requestMethod,
            'requestHeaders' => $this->requestHeaders,
            'requestQuery' => $this->requestQuery,
            'requestPathInfo' => $this->requestPathInfo,
            'requestLanguages' => $this->requestLanguages,
            'requestServer' => $this->requestServer,
            'requestContent' => $this->requestContent,
            'responseStatus' => $this->responseStatus,
            'responseStatusCode' => $this->responseStatusCode,
            'responseHeaders' => $this->responseHeaders,
            'responseContent' => $this->responseContent,
            'userId' => $this->userId,
            'session' => $this->session,
            'sessionValues' => $this->sessionValues,
            'responseTime' => $this->responseTime,
            'routeName' => $this->routeName,
            'routeParameters' => $this->routeParameters,
            'entity' => $this->entity,
            'endpoint' => $this->endpoint,
            'gateway' => $this->gateway,
            'handler' => $this->handler,
            'objectId' => $this->objectId,
            'dateCreated' => $this->dateCreated,
            'dateModified' => $this->dateModified
        ];
    }
}