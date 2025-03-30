<?php

namespace OCA\OpenConnector\Service;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JWadhams\JsonLogic;
use OC\User\NoUserException;
use OCA\OpenConnector\Db\CallLog;
use OCA\OpenConnector\Db\Endpoint;
use OCA\OpenConnector\Db\Mapping;
use OCA\OpenConnector\Db\Rule;
use OCA\OpenConnector\Db\RuleMapper;
use OCA\OpenConnector\Db\Source;
use OCA\OpenConnector\Db\SourceMapper;
use OCA\OpenConnector\Db\MappignMapper;
use OCA\OpenConnector\Db\Synchronization;
use OCA\OpenConnector\Db\SynchronizationMapper;
use OCA\OpenConnector\Db\SynchronizationLog;
use OCA\OpenConnector\Db\SynchronizationLogMapper;
use OCA\OpenConnector\Db\SynchronizationContract;
use OCA\OpenConnector\Db\SynchronizationContractLog;
use OCA\OpenConnector\Db\SynchronizationContractLogMapper;
use OCA\OpenConnector\Db\SynchronizationContractMapper;
use OCA\OpenConnector\Service\CallService;
use OCA\OpenConnector\Service\MappingService;
use OCA\OpenRegister\Db\ObjectEntity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\GenericFileException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IRequest;
use OCP\Lock\LockedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Uid\Uuid;
use OCP\AppFramework\Db\DoesNotExistException;
use Adbar\Dot;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\ISystemTagObjectMapper;
use OCP\Files\File;
use OCP\SystemTag\TagNotFoundException;

use Psr\Container\ContainerInterface;
use DateInterval;
use DateTime;
use OCA\OpenConnector\Db\MappingMapper;
use OCP\AppFramework\Http\NotFoundResponse;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

/**
 * Class SynchronizationService
 *
 * @category Service
 * @package  OCA\OpenConnector\Service
 * @author   Your Name <your.email@example.com>
 * @license  AGPL-3.0
 * @link     https://github.com/nextcloud/server/tree/master/apps/openconnector
 */
class SynchronizationService
{
    private const EXTRA_DATA_CONFIGS_LOCATION          = 'extraDataConfigs';
    private const EXTRA_DATA_DYNAMIC_ENDPOINT_LOCATION = 'dynamicEndpointLocation';
    private const EXTRA_DATA_STATIC_ENDPOINT_LOCATION  = 'staticEndpoint';
    private const KEY_FOR_EXTRA_DATA_LOCATION          = 'keyToSetExtraData';
    private const MERGE_EXTRA_DATA_OBJECT_LOCATION     = 'mergeExtraData';
    private const UNSET_CONFIG_KEY_LOCATION            = 'unsetConfigKey';
    private const FILE_TAG_TYPE                        = 'files';

    private readonly CallService $callService;

    private readonly MappingService $mappingService;

    private readonly ContainerInterface $containerInterface;

    private readonly SynchronizationMapper $synchronizationMapper;

    private readonly SourceMapper $sourceMapper;

    private readonly MappingMapper $mappingMapper;

    private readonly SynchronizationContractMapper $synchronizationContractMapper;

    private readonly SynchronizationContractLogMapper $synchronizationContractLogMapper;

    private readonly SynchronizationLogMapper $synchronizationLogMapper;

    private readonly ObjectService $objectService;

    private readonly StorageService $storageService;

    private readonly RuleMapper $ruleMapper;

    private readonly ISystemTagManager $systemTagManager;

    private readonly ISystemTagObjectMapper $systemTagMapper;


    /**
     * Constructor
     *
     * @param CallService                      $callService                      Call service instance
     * @param MappingService                   $mappingService                   Mapping service instance
     * @param ContainerInterface               $containerInterface               Container interface instance
     * @param SourceMapper                     $sourceMapper                     Source mapper instance
     * @param MappingMapper                    $mappingMapper                    Mapping mapper instance
     * @param SynchronizationMapper            $synchronizationMapper            Synchronization mapper instance
     * @param SynchronizationLogMapper         $synchronizationLogMapper         Synchronization log mapper instance
     * @param SynchronizationContractMapper    $synchronizationContractMapper    Synchronization contract mapper instance
     * @param SynchronizationContractLogMapper $synchronizationContractLogMapper Contract log mapper instance
     * @param ObjectService                    $objectService                    Object service instance
     * @param StorageService                   $storageService                   Storage service instance
     * @param RuleMapper                       $ruleMapper                       Rule mapper instance
     * @param ISystemTagManager                $systemTagManager                 System tag manager instance
     * @param ISystemTagObjectMapper           $systemTagMapper                  System tag mapper instance
     */
    public function __construct(
        CallService $callService,
        MappingService $mappingService,
        ContainerInterface $containerInterface,
        SourceMapper $sourceMapper,
        MappingMapper $mappingMapper,
        SynchronizationMapper $synchronizationMapper,
        SynchronizationLogMapper $synchronizationLogMapper,
        SynchronizationContractMapper $synchronizationContractMapper,
        SynchronizationContractLogMapper $synchronizationContractLogMapper,
        ObjectService $objectService,
        StorageService $storageService,
        RuleMapper $ruleMapper,
        ISystemTagManager $systemTagManager,
        ISystemTagObjectMapper $systemTagMapper
    ) {
        $this->callService                      = $callService;
        $this->mappingService                   = $mappingService;
        $this->containerInterface               = $containerInterface;
        $this->synchronizationMapper            = $synchronizationMapper;
        $this->mappingMapper                    = $mappingMapper;
        $this->synchronizationContractMapper    = $synchronizationContractMapper;
        $this->synchronizationLogMapper         = $synchronizationLogMapper;
        $this->synchronizationContractLogMapper = $synchronizationContractLogMapper;
        $this->sourceMapper                     = $sourceMapper;
        $this->objectService                    = $objectService;
        $this->storageService                   = $storageService;
        $this->ruleMapper                       = $ruleMapper;
        $this->systemTagManager                 = $systemTagManager;
        $this->systemTagMapper                  = $systemTagMapper;

    }//end __construct()


    /**
     * Synchronizes a given synchronization (or a complete source).
     *
     * @param  Synchronization $synchronization
     * @param  bool|null       $isTest          False by default, currently added for synchronziation-test endpoint
     * @param  bool|null       $force           False by default, if true, the object will be updated regardless of changes
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws GuzzleException
     * @throws LoaderError
     * @throws SyntaxError
     * @throws MultipleObjectsReturnedException
     * @throws \OCP\DB\Exception
     * @throws Exception
     * @throws TooManyRequestsHttpException
     */
    public function synchronize(
        Synchronization $synchronization,
        ?bool $isTest=false,
        ?bool $force=false
    ): array {
        // Start execution time measurement
        $startTime = microtime(true);

        // Create log with synchronization ID and initialize results tracking
        $log = [
            'synchronizationId' => $synchronization->getUuid(),
            'result'            => [
                'objects'   => [
                    'found'   => 0,
                    'skipped' => 0,
                    'created' => 0,
                    'updated' => 0,
                    'deleted' => 0,
                    'invalid' => 0,
                ],
                'contracts' => [],
                'logs'      => [],
            ],
            'test'              => $isTest,
            'force'             => $force,
        ];

        // Create initial log entry for tracking purposes
        $log = $this->synchronizationLogMapper->createFromArray($log);

        $sourceConfig = $this->callService->applyConfigDot($synchronization->getSourceConfig());

        // Validate source ID
        if (empty($synchronization->getSourceId()) === true) {
            $errorMessage = 'sourceId of synchronization cannot be empty. Canceling synchronization...';
            $log->setMessage($errorMessage);
            $this->synchronizationLogMapper->update($log);
            throw new Exception($errorMessage);
        }

        // Fetch objects from source
        try {
            $objectList = $this->getAllObjectsFromSource(
                synchronization: $synchronization,
                isTest: $isTest
            );
        } catch (TooManyRequestsHttpException $e) {
            $rateLimitException = $e;
        }

        // Update log with object count
        $result                     = $log->getResult();
        $result['objects']['found'] = count($objectList);

        $synchronizedTargetIds = [];

        // Handle single object case
        if ($sourceConfig['resultsPosition'] === '_object') {
            $objectList                 = [$objectList];
            $result['objects']['found'] = count($objectList);
        }

        // Process each object
        foreach ($objectList as $key => $object) {
            $processResult = $this->processSynchronizationObject(
                synchronization: $synchronization,
                object: $object,
                result: $result,
                isTest: $isTest,
                force: $force,
                log: $log
            );

            $result = $processResult['result'];

            if ($processResult['targetId'] !== null) {
                $synchronizedTargetIds[] = $processResult['targetId'];
            }
        }

        // Handle object deletion
        if ($isTest === false) {
            $result['objects']['deleted'] = $this->deleteInvalidObjects(
                synchronization: $synchronization,
                synchronizedTargetIds: $synchronizedTargetIds
            );
        } else {
            $result['objects']['deleted'] = 0;
        }

        // Process follow-up synchronizations
        foreach ($synchronization->getFollowUps() as $followUp) {
            $followUpSynchronization = $this->synchronizationMapper->find($followUp);
            $this->synchronize(
                synchronization: $followUpSynchronization,
                isTest: $isTest,
                force: $force
            );
        }

        $log->setResult($result);

        // Handle rate limit exception
        if (isset($rateLimitException) === true) {
            $log->setMessage($rateLimitException->getMessage());
            $this->synchronizationLogMapper->update($log);
            throw new TooManyRequestsHttpException(
                message: $rateLimitException->getMessage(),
                code: 429,
                headers: $rateLimitException->getHeaders()
            );
        }

        // Finalize log
        $executionTime = round((microtime(true) - $startTime) * 1000);
        $log->setExecutionTime($executionTime);
        $log->setMessage('Success');
        $this->synchronizationLogMapper->update($log);

        return $log->jsonSerialize();

    }//end synchronize()


    /**
     * Gets id from object as is in the origin
     *
     * @param Synchronization $synchronization
     * @param array           $object
     *
     * @return string|int id
     * @throws Exception
     */
    private function getOriginId(Synchronization $synchronization, array $object): (int | string)
    {
        // Default ID position is 'id' if not specified in source config
        $originIdPosition = 'id';
        $sourceConfig     = $synchronization->getSourceConfig();

        // Check if a custom ID position is defined in the source configuration
        if (isset($sourceConfig['idPosition']) === true && empty($sourceConfig['idPosition']) === false) {
            // Override default with custom ID position from config
            $originIdPosition = $sourceConfig['idPosition'];
        }

        // Create Dot object for easy access to nested array values
        $objectDot = new Dot($object);

        // Try to get the ID value from the specified position in the object
        $originId = $objectDot->get($originIdPosition);

        // If no ID was found at the specified position, throw an error
        if ($originId === null) {
            throw new Exception('Could not find origin id in object for key: '.$originIdPosition);
        }

        // Return the found ID value
        return $originId;

    }//end getOriginId()


    /**
     * Fetch an object from a specific endpoint.
     *
     * @param Synchronization $synchronization The synchronization containing the source.
     * @param string          $endpoint        The endpoint to request to fetch the desired object.
     *
     * @return array The resulting object.
     *
     * @throws GuzzleException
     * @throws LoaderError
     * @throws SyntaxError
     * @throws \OCP\DB\Exception
     */
    public function getObjectFromSource(Synchronization $synchronization, string $endpoint): array
    {
        $source = $this->sourceMapper->find(id: $synchronization->getSourceId());

        // Let's get the source config
        $sourceConfig = $this->callService->applyConfigDot($synchronization->getSourceConfig());

        $config = [];
        if (empty($sourceConfig['headers']) === false) {
            $config['headers'] = $sourceConfig['headers'];
        }

        if (empty($sourceConfig['query']) === false) {
            $config['query'] = $sourceConfig['query'];
        }

        if (str_starts_with($endpoint, $source->getLocation()) === true) {
            $endpoint = str_replace(search: $source->getLocation(), replace: '', subject: $endpoint);
        }

        // Make the initial API call, read denotes that we call an endpoint for a single object (for config variations).
        $response = $this->callService->call(source: $source, endpoint: $endpoint, config: $config, read: true)->getResponse();

        return json_decode($response['body'], true);

    }//end getObjectFromSource()


    /**
     * Fetches additional data for a given object based on the synchronization configuration.
     *
     * @param Synchronization     $synchronization The synchronization instance containing configuration details
     * @param array<string,mixed> $extraDataConfig The configuration array for retrieving extra data
     * @param array<string,mixed> $object          The original object for which extra data needs to be fetched
     * @param string|null         $originId        Optional origin ID for static endpoint configuration
     *
     * @return array<string,mixed> The object merged with extra data or the extra data itself
     *
     * @throws Exception|GuzzleException If endpoint configuration is invalid or endpoint cannot be determined
     *
     * @psalm-return array<string,mixed>
     */
    private function fetchExtraDataForObject(
        Synchronization $synchronization,
        array $extraDataConfig,
        array $object,
        ?string $originId=null
    ): array {
        // Return original object if no endpoint configuration exists
        if (isset($extraDataConfig[self::EXTRA_DATA_DYNAMIC_ENDPOINT_LOCATION]) === false
            && isset($extraDataConfig[self::EXTRA_DATA_STATIC_ENDPOINT_LOCATION]) === false
        ) {
            return $object;
        }

        $endpoint = null;

        // Get endpoint from object if dynamic endpoint is configured
        if (isset($extraDataConfig[self::EXTRA_DATA_DYNAMIC_ENDPOINT_LOCATION]) === true) {
            $dotObject = new Dot($object);
            $endpoint  = $dotObject->get($extraDataConfig[self::EXTRA_DATA_DYNAMIC_ENDPOINT_LOCATION] ?? null);
        }

        // Get endpoint from static configuration if available
        if (isset($extraDataConfig[self::EXTRA_DATA_STATIC_ENDPOINT_LOCATION]) === true) {
            if ($originId === null) {
                $originId = $this->getOriginId($synchronization, $object);
            }

            // Override origin ID if endpoint ID location is specified
            if (isset($extraDataConfig['endpointIdLocation']) === true) {
                $dotObject = new Dot($object);
                $originId  = $dotObject->get($extraDataConfig['endpointIdLocation']);
            }

            $endpoint = $extraDataConfig[self::EXTRA_DATA_STATIC_ENDPOINT_LOCATION];

            // Replace origin ID placeholders in endpoint
            $endpoint = str_replace(
                search: [
                    '{{ originId }}',
                    '{{originId}}',
                ],
                replace: $originId,
                subject: $endpoint
            );

            // Handle sub-object ID replacement if configured
            if (isset($extraDataConfig['subObjectId']) === true) {
                $objectDot   = new Dot($object);
                $subObjectId = $objectDot->get($extraDataConfig['subObjectId']);
                if ($subObjectId !== null) {
                    $endpoint = str_replace(
                        search: [
                            '{{ subObjectId }}',
                            '{{subObjectId}}',
                        ],
                        replace: $subObjectId,
                        subject: $endpoint
                    );
                }
            }
        }//end if

        // Validate endpoint existence
        if (!$endpoint) {
            throw new Exception(
                sprintf(
                    'Could not get static or dynamic endpoint, object: %s',
                    json_encode($object)
                )
            );
        }

        // Handle config key unset if specified
        $sourceConfig = $synchronization->getSourceConfig();
        if (isset($extraDataConfig[self::UNSET_CONFIG_KEY_LOCATION]) === true
            && isset($sourceConfig[$extraDataConfig[self::UNSET_CONFIG_KEY_LOCATION]]) === true
        ) {
            unset($sourceConfig[$extraDataConfig[self::UNSET_CONFIG_KEY_LOCATION]]);
            $synchronization->setSourceConfig($sourceConfig);
        }

        // Fetch extra data from source
        $extraData = $this->getObjectFromSource($synchronization, $endpoint);

        // Handle per-result extra data configuration
        if (isset($extraDataConfig['extraDataConfigPerResult']) === true) {
            $dotObject = new Dot($extraData);
            $results   = $dotObject->get($extraDataConfig['resultsLocation']);

            foreach ($results as $key => $result) {
                $results[$key] = $this->fetchExtraDataForObject(
                    synchronization: $synchronization,
                    extraDataConfig: $extraDataConfig['extraDataConfigPerResult'],
                    object: $result,
                    originId: $originId
                );
            }

            $extraData = $results;
        }

        // Set custom key for extra data if configured
        if (isset($extraDataConfig[self::KEY_FOR_EXTRA_DATA_LOCATION]) === true) {
            $extraData = [$extraDataConfig[self::KEY_FOR_EXTRA_DATA_LOCATION] => $extraData];
        }

        // Merge with original object if configured
        if (isset($extraDataConfig[self::MERGE_EXTRA_DATA_OBJECT_LOCATION]) === true
            && ($extraDataConfig[self::MERGE_EXTRA_DATA_OBJECT_LOCATION] === true
            || $extraDataConfig[self::MERGE_EXTRA_DATA_OBJECT_LOCATION] === 'true')
        ) {
            return array_merge($object, $extraData);
        }

        return $extraData;

    }//end fetchExtraDataForObject()


    /**
     * Fetches multiple extra data entries for an object
     *
     * @param Synchronization     $synchronization The synchronization instance
     * @param array<string,mixed> $sourceConfig    The source configuration
     * @param array<string,mixed> $object          The original object
     *
     * @return array<string,mixed> The updated object with merged extra data
     *
     * @throws GuzzleException
     * @throws Exception
     *
     * @psalm-return array<string,mixed>
     */
    private function fetchMultipleExtraData(
        Synchronization $synchronization,
        array $sourceConfig,
        array $object
    ): array {
        if (isset($sourceConfig[self::EXTRA_DATA_CONFIGS_LOCATION]) === true) {
            foreach ($sourceConfig[self::EXTRA_DATA_CONFIGS_LOCATION] as $extraDataConfig) {
                $object = array_merge(
                    $object,
                    $this->fetchExtraDataForObject($synchronization, $extraDataConfig, $object)
                );
            }
        }

        return $object;

    }//end fetchMultipleExtraData()


    /**
     * Maps an object using source hash mapping configuration
     *
     * @param Synchronization     $synchronization The synchronization instance
     * @param array<string,mixed> $object          The input object to map
     *
     * @return array<string,mixed>|Exception The mapped object or Exception if mapping fails
     *
     * @throws LoaderError
     * @throws SyntaxError
     *
     * @psalm-return array<string,mixed>|Exception
     */
    private function mapHashObject(
        Synchronization $synchronization,
        array $object
    ): (array | Exception) {
        if (empty($synchronization->getSourceHashMapping()) === false) {
            try {
                $sourceHashMapping = $this->mappingMapper->find(
                    id: $synchronization->getSourceHashMapping()
                );
            } catch (DoesNotExistException $exception) {
                return new Exception($exception->getMessage());
            }

            // Execute mapping if found
            if ($sourceHashMapping) {
                return $this->mappingService->executeMapping(
                    mapping: $sourceHashMapping,
                    input: $object
                );
            }
        }

        return $object;

    }//end mapHashObject()


    /**
     * Deletes invalid objects associated with a synchronization
     *
     * @param Synchronization    $synchronization       The synchronization entity
     * @param array<string>|null $synchronizedTargetIds Valid target IDs
     *
     * @return int Number of deleted objects
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \OCP\DB\Exception
     *
     * @psalm-return int
     */
    public function deleteInvalidObjects(
        Synchronization $synchronization,
        ?array $synchronizedTargetIds=[]
    ): int {
        $deletedObjectsCount = 0;
        $type                = $synchronization->getTargetType();

        switch ($type) {
        case 'register/schema':
            $targetIdsToDelete    = [];
            [
                $registerId,
                $schemaId,
            ]                     = explode(separator: '/', string: $synchronization->getTargetId());
            $allContracts         = $this->synchronizationContractMapper->findAllBySynchronizationAndSchema(synchronizationId: $synchronization->getId(), schemaId: $schemaId);
            $allContractTargetIds = [];
            foreach ($allContracts as $contract) {
                if ($contract->getTargetId() !== null) {
                    $allContractTargetIds[] = $contract->getTargetId();
                }
            }

            // Initialize $synchronizedTargetIds as empty array if null
            if ($synchronizedTargetIds === null) {
                $synchronizedTargetIds = [];
            }

            // Check if we have contracts that became invalid or do not exist in the source anymore
            $targetIdsToDelete = array_diff($allContractTargetIds, $synchronizedTargetIds);

            foreach ($targetIdsToDelete as $targetIdToDelete) {
                try {
                    $synchronizationContract = $this->synchronizationContractMapper->findOnTarget(synchronization: $synchronization->getId(), targetId: $targetIdToDelete);
                    if ($synchronizationContract === null) {
                        continue;
                    }

                    $synchronizationContract = $this->updateTarget(synchronizationContract: $synchronizationContract, targetObject: [], action: 'delete');
                    $this->synchronizationContractMapper->update($synchronizationContract);
                    $deletedObjectsCount++;
                } catch (DoesNotExistException $exception) {
                    // @todo log
                }
            }
            break;
        }//end switch

        return $deletedObjectsCount;

    }//end deleteInvalidObjects()


    /**
     * Synchronize a contract with source data
     *
     * @param SynchronizationContract $synchronizationContract The contract to synchronize
     * @param Synchronization|null    $synchronization         The synchronization configuration
     * @param array<string,mixed>     $object                  The source object data
     * @param bool|null               $isTest                  Whether this is a test run
     * @param bool|null               $force                   Whether to force synchronization
     * @param SynchronizationLog|null $log                     The log to update
     *
     * @return SynchronizationContract|Exception|array<string,mixed> The synchronization result
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws LoaderError
     * @throws SyntaxError
     * @throws GuzzleException
     *
     * @psalm-return SynchronizationContract|Exception|array{log: array<string,mixed>, contract: array<string,mixed>}
     */
    public function synchronizeContract(
        SynchronizationContract $synchronizationContract,
        ?Synchronization $synchronization=null,
        array $object=[],
        ?bool $isTest=false,
        ?bool $force=false,
        ?SynchronizationLog $log=null
    ): (SynchronizationContract | Exception | array) {
        $contractLog = null;

        // We are doing something so lets log it
        if ($synchronizationContract->getId() !== null) {
            $contractLog = $this->synchronizationContractLogMapper->createFromArray(
                [
                    'synchronizationId'         => $synchronization->getId(),
                    'synchronizationContractId' => $synchronizationContract->getId(),
                    'source'                    => $object,
                    'test'                      => $isTest,
                    'force'                     => $force,
                ]
            );
        }

        if (isset($contractLog) === true) {
            $contractLog->setSynchronizationLogId($log->getId());
        }

        $sourceConfig = $this->callService->applyConfigDot($synchronization->getSourceConfig());

        // Check if extra data needs to be fetched
        $object = $this->fetchMultipleExtraData(synchronization: $synchronization, sourceConfig: $sourceConfig, object: $object);

        // Get mapped hash object (some fields can make it look the object has changed even if it hasn't)
        $hashObject = $this->mapHashObject(synchronization: $synchronization, object: $object);
        // Let create a source hash for the object
        $originHash = md5(serialize($hashObject));

        // If no source target mapping is defined, use original object
        if (empty($synchronization->getSourceTargetMapping()) === true) {
            $sourceTargetMapping = null;
        } else {
            try {
                $sourceTargetMapping = $this->mappingMapper->find(id: $synchronization->getSourceTargetMapping());
            } catch (DoesNotExistException $exception) {
                return new Exception($exception->getMessage());
            }
        }

        // Let's prevent pointless updates by checking:
        // 1. If the origin hash matches (object hasn't changed)
        // 2. If the synchronization config hasn't been updated since last check
        // 3. If source target mapping exists, check it hasn't been updated since last check
        // 4. If target ID and hash exist (object hasn't been removed from target)
        // 5. Force parameter is false (otherwise always continue with update)
        if ($force === false
            && $originHash === $synchronizationContract->getOriginHash()
            && $synchronization->getUpdated() < $synchronizationContract->getSourceLastChecked()
            && ($sourceTargetMapping === null
            || $sourceTargetMapping->getUpdated() < $synchronizationContract->getSourceLastChecked())
            && $synchronizationContract->getTargetId() !== null
            && $synchronizationContract->getTargetHash() !== null
        ) {
            // We checked the source so let log that
            $synchronizationContract->setSourceLastChecked(new DateTime());
            // The object has not changed and neither config nor mapping have been updated since last check
            $contractLog = $this->synchronizationContractLogMapper->update($contractLog);
            return [
                'log'      => $contractLog->jsonSerialize(),
                'contract' => $synchronizationContract->jsonSerialize(),
            ];
        }

        // The object has changed, oke let do mappig and set metadata
        $synchronizationContract->setOriginHash($originHash);
        $synchronizationContract->setSourceLastChanged(new DateTime());
        $synchronizationContract->setSourceLastChecked(new DateTime());

        // Execute mapping if found
        if ($sourceTargetMapping) {
            $targetObject = $this->mappingService->executeMapping(mapping: $sourceTargetMapping, input: $object);
        } else {
            $targetObject = $object;
        }

        if (isset($contractLog) === true) {
            $contractLog->setTarget($targetObject);
        }

        if ($synchronization->getActions() !== []) {
            $targetObject = $this->processRules(synchronization: $synchronization, data: $targetObject, timing: 'before');
        }

            // set the target hash
        $targetHash = md5(serialize($targetObject));

        $synchronizationContract->setTargetHash($targetHash);
        $synchronizationContract->setTargetLastChanged(new DateTime());
        $synchronizationContract->setTargetLastSynced(new DateTime());
        $synchronizationContract->setSourceLastSynced(new DateTime());

        // Handle synchronization based on test mode
        if ($isTest === true) {
            // Return test data without updating target
            $contractLog->setTargetResult('test');
            $contractLog = $this->synchronizationContractLogMapper->update($contractLog);
            return [
                'log'      => $contractLog->jsonSerialize(),
                'contract' => $synchronizationContract->jsonSerialize(),
            ];
        }

        // Update target and create log when not in test mode
        $synchronizationContract = $this->updateTarget(
            synchronizationContract: $synchronizationContract,
            targetObject: $targetObject
        );

        if ($synchronization->getTargetType() === 'register/schema') {
            [
                $registerId,
                $schemaId,
            ] = explode(separator: '/', string: $synchronization->getTargetId());
            $this->processRules(synchronization: $synchronization, data: $targetObject, timing: 'after', objectId: $synchronizationContract->getTargetId(), registerId: $registerId, schemaId: $schemaId);
        }

        // Create log entry for the synchronization
        if (isset($contractLog) === true) {
            $contractLog->setTargetResult($synchronizationContract->getTargetLastAction());
            $contractLog = $this->synchronizationContractLogMapper->update($contractLog);
        }

        if ($synchronizationContract->getId()) {
            $synchronizationContract = $this->synchronizationContractMapper->update($synchronizationContract);
        } else {
            if ($synchronizationContract->getUuid() === null) {
                $synchronizationContract->setUuid(Uuid::v4());
            }

            $synchronizationContract = $this->synchronizationContractMapper->insertOrUpdate($synchronizationContract);
        }

        return [
            'log'      => $contractLog ? $contractLog->jsonSerialize() : [],
            'contract' => $synchronizationContract->jsonSerialize(),
        ];

    }//end synchronizeContract()


    /**
     * Updates or deletes a target object in the Open Register system
     *
     * @param SynchronizationContract  $synchronizationContract The contract being updated
     * @param Synchronization          $synchronization         The synchronization configuration
     * @param array<string,mixed>|null $targetObject            The target object data
     * @param string|null              $action                  The action to perform ('save'|'delete')
     *
     * @return SynchronizationContract The updated synchronization contract
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @psalm-param 'save'|'delete' $action
     */
    private function updateTargetOpenRegister(
        SynchronizationContract $synchronizationContract,
        Synchronization $synchronization,
        ?array $targetObject=[],
        ?string $action='save'
    ): SynchronizationContract {
        // Initialize object service
        $objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');
        $sourceConfig  = $this->callService->applyConfigDot($synchronization->getSourceConfig());

        // Set target ID if exists
        if ($synchronizationContract->getTargetId() !== null) {
            $targetObject['id'] = $synchronizationContract->getTargetId();
        }

        // Update sub-object IDs if configured
        if (isset($sourceConfig['subObjects']) === true) {
            $targetObject = $this->updateIdsOnSubObjects(
                subObjectsConfig: $sourceConfig['subObjects'],
                synchronizationId: $synchronization->getId(),
                targetObject: $targetObject
            );
        }

        // Parse target ID for register and schema
        [
            $register,
            $schema,
        ] = explode('/', $synchronization->getTargetId());

        // Process based on action type
        switch ($action) {
        case 'save':
            $target = $objectService->saveObject(
                register: $register,
                schema: $schema,
                object: $targetObject
            );

            $synchronizationContract->setTargetId($target->getUuid());

            // Handle sub-objects if configured
            if (isset($sourceConfig['subObjects']) === true) {
                $targetObject = $objectService->renderEntity($target->jsonSerialize(), ['all']);
                $this->updateContractsForSubObjects(
                    subObjectsConfig: $sourceConfig['subObjects'],
                    synchronizationId: $synchronization->getId(),
                    targetObject: $targetObject
                );
            }

            // Set action based on operation type
            $synchronizationContract->setTargetLastAction(
                $synchronizationContract->getTargetId() ? 'update' : 'create'
            );
            break;

        case 'delete':
            $objectService->deleteObject(
                register: $register,
                schema: $schema,
                uuid: $synchronizationContract->getTargetId()
            );

            $synchronizationContract->setTargetId(null);
            $synchronizationContract->setTargetLastAction('delete');
            break;
        }//end switch

        return $synchronizationContract;

    }//end updateTargetOpenRegister()


    /**
     * Updates synchronization contracts for sub-objects
     *
     * @param array<string,mixed> $subObjectsConfig  The sub-objects configuration
     * @param string              $synchronizationId The synchronization ID
     * @param array<string,mixed> $targetObject      The target object with sub-objects
     *
     * @return void
     *
     * @throws \OCP\DB\Exception
     */
    private function updateContractsForSubObjects(
        array $subObjectsConfig,
        string $synchronizationId,
        array $targetObject
    ): void {
        foreach ($subObjectsConfig as $propertyName => $subObjectConfig) {
            if (isset($targetObject[$propertyName]) === false) {
                continue;
            }

            $propertyData = $targetObject[$propertyName];

            // Process associative array sub-objects
            if (is_array($propertyData) && $this->isAssociativeArray($propertyData)) {
                if (isset($propertyData['originId'])) {
                    $this->processSyncContract($synchronizationId, $propertyData);
                }

                // Process nested sub-objects
                foreach ($propertyData as $key => $value) {
                    if (is_array($value) === true
                        && isset($subObjectConfig['subObjects']) === true
                    ) {
                        $this->updateContractsForSubObjects(
                            $subObjectConfig['subObjects'],
                            $synchronizationId,
                            [$key => $value]
                        );
                    }
                }
            }

            // Process indexed array of sub-objects
            if (is_array($propertyData) === true && !$this->isAssociativeArray($propertyData)) {
                foreach ($propertyData as $subObjectData) {
                    if (is_array($subObjectData) === true
                        && isset($subObjectData['originId']) === true
                    ) {
                        $this->processSyncContract($synchronizationId, $subObjectData);
                    }

                    // Process nested sub-objects
                    if (is_array($subObjectData) === true
                        && isset($subObjectConfig['subObjects']) === true
                    ) {
                        $this->updateContractsForSubObjects(
                            $subObjectConfig['subObjects'],
                            $synchronizationId,
                            $subObjectData
                        );
                    }
                }
            }//end if
        }//end foreach

    }//end updateContractsForSubObjects()


    /**
     * Process a single synchronization contract for a sub-object
     *
     * @param string              $synchronizationId The synchronization ID
     * @param array<string,mixed> $subObjectData     The sub-object data
     *
     * @return void
     *
     * @throws \OCP\DB\Exception
     */
    private function processSyncContract(
        string $synchronizationId,
        array $subObjectData
    ): void {
        // Extract nested ID if exists
        $id = ($subObjectData['id']['id']['id']['id'] ?? $subObjectData['id']['id']['id'] ?? $subObjectData['id']['id'] ?? $subObjectData['id']);

        // Find existing contract or create new one
        $subContract = $this->synchronizationContractMapper->findByOriginId(
            originId: $subObjectData['originId']
        );

        if (!$subContract) {
            $subContract = new SynchronizationContract();
            $subContract->setSynchronizationId($synchronizationId);
            $subContract->setOriginId($subObjectData['originId']);
            $subContract->setTargetId($id);
            $subContract->setUuid(Uuid::V4());
            $subContract->setTargetHash(md5(serialize($subObjectData)));
            $subContract->setTargetLastChanged(new DateTime());
            $subContract->setTargetLastSynced(new DateTime());
            $subContract->setSourceLastSynced(new DateTime());

            $subContract = $this->synchronizationContractMapper->insert($subContract);
        } else {
            $subContract = $this->synchronizationContractMapper->updateFromArray(
                id: $subContract->getId(),
                object: [
                    'synchronizationId' => $synchronizationId,
                    'originId'          => $subObjectData['originId'],
                    'targetId'          => $id,
                    'targetHash'        => md5(serialize($subObjectData)),
                    'targetLastChanged' => new DateTime(),
                    'targetLastSynced'  => new DateTime(),
                    'sourceLastSynced'  => new DateTime(),
                ]
            );
        }//end if

        // Create contract log
        $this->synchronizationContractLogMapper->createFromArray(
            [
                'synchronizationId'         => $subContract->getSynchronizationId(),
                'synchronizationContractId' => $subContract->getId(),
                'target'                    => $subObjectData,
                'expires'                   => new DateTime('+1 day'),
            ]
        );

    }//end processSyncContract()


    /**
     * Check if an array is associative
     *
     * @param array<string|int,mixed> $array The array to check
     *
     * @return bool True if associative, false otherwise
     *
     * @psalm-pure
     */
    private function isAssociativeArray(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;

    }//end isAssociativeArray()


    /**
     * Processes subObjects update their arrays with existing targetId's so OpenRegister can update the objects instead of duplicate them.
     *
     * @param array     $subObjectsConfig     The configuration for subObjects.
     * @param string    $synchronizationId    The ID of the synchronization.
     * @param array     $targetObject         The target object containing subObjects to be processed.
     * @param bool|null $parentIsNumericArray Whether the parent object is a numeric array (default false).
     *
     * @return array The updated target object with IDs updated on subObjects.
     */
    private function updateIdsOnSubObjects(array $subObjectsConfig, string $synchronizationId, array $targetObject, ?bool $parentIsNumericArray=false): array
    {
        foreach ($subObjectsConfig as $propertyName => $subObjectConfig) {
            if (isset($targetObject[$propertyName]) === false) {
                continue;
            }

            // If property data is an array of sub-objects, iterate and process
            if (is_array($targetObject[$propertyName]) === true) {
                if (isset($targetObject[$propertyName]['originId']) === true) {
                    $targetObject[$propertyName] = $this->updateIdOnSubObject($synchronizationId, $targetObject[$propertyName]);
                }

                // Recursively process any nested sub-objects within the associative array
                foreach ($targetObject[$propertyName] as $key => $value) {
                    if (is_array($value) === true && isset($subObjectConfig['subObjects'][$key]) === true) {
                        if ($this->isAssociativeArray($value) === true) {
                            $targetObject[$propertyName][$key] = $this->updateIdsOnSubObjects($subObjectConfig['subObjects'], $synchronizationId, [$key => $value]);
                        } else if (is_array($value) === true && $this->isAssociativeArray(reset($value)) === true) {
                            foreach ($value as $iterativeSubArrayKey => $iterativeSubArray) {
                                $targetObject[$propertyName][$key][$iterativeSubArrayKey] = $this->updateIdsOnSubObjects($subObjectConfig['subObjects'], $synchronizationId, [$key => $iterativeSubArray], true);
                            }
                        }
                    }
                }
            }
        }//end foreach

        if ($parentIsNumericArray === true) {
            return reset($targetObject);
        }

        return $targetObject;

    }//end updateIdsOnSubObjects()


    /**
     * Updates the ID of a single subObject based on its synchronization contract so OpenRegister can update the object .
     *
     * @param string $synchronizationId The ID of the synchronization.
     * @param array  $subObject         The subObject to update.
     *
     * @return array The updated subObject with the ID set based on the synchronization contract.
     * @throws MultipleObjectsReturnedException
     * @throws \OCP\DB\Exception
     */
    private function updateIdOnSubObject(string $synchronizationId, array $subObject): array
    {
        if (isset($subObject['originId']) === true) {
            $subObjectContract = $this->synchronizationContractMapper->findSyncContractByOriginId(
                synchronizationId: $synchronizationId,
                originId: $subObject['originId']
            );

            if ($subObjectContract !== null) {
                   $subObject['id'] = $subObjectContract->getTargetId();
            }
        }

        return $subObject;

    }//end updateIdOnSubObject()


    /**
     * Write the data to the target
     *
     * @param SynchronizationContract $synchronizationContract
     * @param array|null              $targetObject
     * @param string|null             $action                  Determines what needs to be done with the target object, defaults to 'save'
     *
     * @return SynchronizationContract
     * @throws ContainerExceptionInterface
     * @throws GuzzleException
     * @throws LoaderError
     * @throws NotFoundExceptionInterface
     * @throws SyntaxError
     * @throws \OCP\DB\Exception
     * @throws Exception
     */
    public function updateTarget(SynchronizationContract $synchronizationContract, ?array $targetObject=[], ?string $action='save'): SynchronizationContract
    {
        // The function can be called solo set let's make sure we have the full synchronization object
        if (isset($synchronization) === false) {
            $synchronization = $this->synchronizationMapper->find($synchronizationContract->getSynchronizationId());
        }

        // Let's check if we need to create or update
        $update = false;
        if ($synchronizationContract->getTargetId()) {
            $update = true;
        }

        $type = $synchronization->getTargetType();

        switch ($type) {
        case 'register/schema':
            $synchronizationContract = $this->updateTargetOpenRegister(synchronizationContract: $synchronizationContract, synchronization: $synchronization, targetObject: $targetObject, action: $action);
            break;
        case 'api':
            $targetConfig            = $synchronization->getTargetConfig();
            $synchronizationContract = $this->writeObjectToTarget(synchronization: $synchronization, contract: $synchronizationContract, endpoint: ($targetConfig['endpoint'] ?? ''));
            break;
        case 'database':
            // @todo: implement
            break;
        default:
            throw new Exception("Unsupported target type: $type");
        }

        return $synchronizationContract;

    }//end updateTarget()


    /**
     * Get all the object from a source
     *
     * @param Synchronization $synchronization
     * @param bool|null       $isTest          False by default, currently added for synchronziation-test endpoint
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws GuzzleException
     * @throws NotFoundExceptionInterface
     * @throws \OCP\DB\Exception
     */
    public function getAllObjectsFromSource(Synchronization $synchronization, ?bool $isTest=false): array
    {
        $objects = [];

        $type = $synchronization->getSourceType();

        switch ($type) {
        case 'register/schema':
            // @todo: implement
            break;
        case 'api':
            $objects = $this->getAllObjectsFromApi(synchronization: $synchronization, isTest: $isTest);
            break;
        case 'database':
            // @todo: implement
            break;
        }

        return $objects;

    }//end getAllObjectsFromSource()


    /**
     * Fetch all objects from an API source for a given synchronization.
     *
     * @param Synchronization $synchronization The synchronization object containing source information.
     * @param bool|null       $isTest          If true, only a single object is returned for testing purposes.
     *
     * @return array An array of all objects retrieved from the API.
     * @throws GuzzleException
     * @throws LoaderError
     * @throws SyntaxError
     * @throws \OCP\DB\Exception
     */
    public function getAllObjectsFromApi(Synchronization $synchronization, ?bool $isTest=false): array
    {
        $source = $this->sourceMapper->find($synchronization->getSourceId());

        // Check rate limit before proceeding
        $this->checkRateLimit($source);

        // Extract source configuration
        $sourceConfig = $this->callService->applyConfigDot($synchronization->getSourceConfig());
        $endpoint     = ($sourceConfig['endpoint'] ?? '');
        $headers      = ($sourceConfig['headers'] ?? []);
        $query        = ($sourceConfig['query'] ?? []);

        // Determine if pagination is used
        $usesPagination = true;
        if (isset($sourceConfig['usesPagination']) === true) {
            $usesPagination = filter_var(
                $sourceConfig['usesPagination'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        // Build request configuration
        $config = [];
        if (empty($headers) === false) {
            $config['headers'] = $headers;
        }

        if (empty($query) === false) {
            $config['query'] = $query;
        }

        // Set current page based on rate limit
        $currentPage = 1;
        if ($source->getRateLimitLimit() !== null) {
            $currentPage = ($synchronization->getCurrentPage() ?? 1);
        }

        // Fetch all pages recursively
        $objects = $this->fetchAllPages(
            source: $source,
            endpoint: $endpoint,
            config: $config,
            synchronization: $synchronization,
            currentPage: $currentPage,
            isTest: $isTest,
            usesPagination: $usesPagination
        );

        // Reset page counter after synchronization
        if ($isTest === false) {
            $synchronization->setCurrentPage(1);
            $this->synchronizationMapper->update($synchronization);
        }

        return $objects;

    }//end getAllObjectsFromApi()


    /**
     * Recursively fetch all pages of data from the API
     *
     * @param Source              $source           The source configuration
     * @param string              $endpoint         The API endpoint
     * @param array<string,mixed> $config           The request configuration
     * @param Synchronization     $synchronization  The synchronization state
     * @param int                 $currentPage      The current page number
     * @param bool                $isTest           Whether this is a test run
     * @param bool|null           $usesNextEndpoint Whether to use next endpoint pagination
     * @param bool|null           $usesPagination   Whether to use pagination
     *
     * @return array<string,mixed> The retrieved objects
     *
     * @throws GuzzleException
     * @throws TooManyRequestsHttpException
     * @throws LoaderError
     * @throws SyntaxError
     * @throws \OCP\DB\Exception
     */
    private function fetchAllPages(
        Source $source,
        string $endpoint,
        array $config,
        Synchronization $synchronization,
        int $currentPage,
        bool $isTest=false,
        ?bool $usesNextEndpoint=null,
        ?bool $usesPagination=true
    ): array {
        // Make API call
        $callLog  = $this->callService->call(
            source: $source,
            endpoint: $endpoint,
            config: $config
        );
        $response = $callLog->getResponse();

        // Handle rate limiting
        if ($response === null && $callLog->getStatusCode() === 429) {
            throw new TooManyRequestsHttpException(
                message: "Rate Limit on Source exceeded.",
                code: 429,
                headers: $this->getRateLimitHeaders($source)
            );
        }

        $body = $response['body'];

        // Parse response body (JSON or XML)
        $result = json_decode($body, true);

        // Try XML if JSON parsing failed
        if (empty($result) === true) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($body, "SimpleXMLElement", LIBXML_NOCDATA);

            if ($xml !== false) {
                $result = $this->xmlToArray($xml);
            }
        }

        // Return empty array if response is unparseable
        if (empty($result) === true) {
            return [];
        }

        // Process current page
        $objects = $this->getAllObjectsFromArray(
            array: $result,
            synchronization: $synchronization
        );

        // Return objects if pagination is disabled
        if ($usesPagination === false) {
            return $objects;
        }

        // Return first object only in test mode
        if ($isTest === true) {
            return ([$objects[0]] ?? []);
        }

        // Return all objects for XML responses (no pagination)
        if (isset($xml) && $xml !== false) {
            return $objects;
        }

        // Update page counter
        $currentPage++;
        $synchronization->setCurrentPage($currentPage);
        $this->synchronizationMapper->update($synchronization);

        // Handle pagination
        $nextEndpoint    = $endpoint;
        $newNextEndpoint = null;

        // Determine pagination type
        if (array_key_exists('next', $result) && $usesNextEndpoint === null) {
            $usesNextEndpoint = true;
        }

        // Get next endpoint if using endpoint-based pagination
        if ($usesNextEndpoint !== false) {
            $newNextEndpoint = $this->getNextEndpoint(
                body: $result,
                url: $source->getLocation()
            );
        }

        // Update pagination configuration
        if ($newNextEndpoint !== null && $newNextEndpoint !== $endpoint) {
            $nextEndpoint     = $newNextEndpoint;
            $usesNextEndpoint = true;
        } else if ($newNextEndpoint === null && $usesNextEndpoint !== true) {
            $usesNextEndpoint = false;
            $config           = $this->getNextPage(
                config: $config,
                sourceConfig: $synchronization->getSourceConfig(),
                currentPage: $currentPage
            );
        }

        // Check if pagination should continue
        if (($usesNextEndpoint === true && ($newNextEndpoint === null || $newNextEndpoint === $endpoint))
            || ($usesNextEndpoint === false && ($objects === null || empty($objects) === true))
        ) {
            return $objects;
        }

        // Fetch next page and merge results
        $objects = array_merge(
            $objects,
            $this->fetchAllPages(
                source: $source,
                endpoint: $nextEndpoint,
                config: $config,
                synchronization: $synchronization,
                currentPage: $currentPage,
                isTest: $isTest,
                usesNextEndpoint: $usesNextEndpoint
            )
        );

        return $objects;

    }//end fetchAllPages()


    /**
     * Checks if the source has exceeded its rate limit and throws an exception if true.
     *
     * @param Source $source The source object containing rate limit details.
     *
     * @throws TooManyRequestsHttpException
     */
    private function checkRateLimit(Source $source): void
    {
        if ($source->getRateLimitRemaining() !== null
            && $source->getRateLimitReset() !== null
            && $source->getRateLimitRemaining() <= 0
            && $source->getRateLimitReset() > time()
        ) {
            throw new TooManyRequestsHttpException(
                message: "Rate Limit on Source has been exceeded. Canceling synchronization...",
                code: 429,
                headers: $this->getRateLimitHeaders($source)
            );
        }

    }//end checkRateLimit()


    /**
     * Retrieves rate limit information from a given source and formats it as HTTP headers.
     *
     * This function extracts rate limit details from the provided source object and returns them
     * as an associative array of headers. The headers can be used for communicating rate limit status
     * in API responses or logging purposes.
     *
     * @param Source $source The source object containing rate limit details, such as limits, remaining requests, and reset times.
     *
     * @return array An associative array of rate limit headers:
     *               - 'X-RateLimit-Limit' (int|null): The maximum number of allowed requests.
     *               - 'X-RateLimit-Remaining' (int|null): The number of requests remaining in the current window.
     *               - 'X-RateLimit-Reset' (int|null): The Unix timestamp when the rate limit resets.
     *               - 'X-RateLimit-Used' (int|null): The number of requests used so far.
     *               - 'X-RateLimit-Window' (int|null): The duration of the rate limit window in seconds.
     */
    private function getRateLimitHeaders(Source $source): array
    {
        return [
            'X-RateLimit-Limit'     => $source->getRateLimitLimit(),
            'X-RateLimit-Remaining' => $source->getRateLimitRemaining(),
            'X-RateLimit-Reset'     => $source->getRateLimitReset(),
            'X-RateLimit-Used'      => 0,
            'X-RateLimit-Window'    => $source->getRateLimitWindow(),
        ];

    }//end getRateLimitHeaders()


    /**
     * Updates the API request configuration with pagination details for the next page.
     *
     * @param array $config       The current request configuration.
     * @param array $sourceConfig The source configuration containing pagination settings.
     * @param int   $currentPage  The current page number for pagination.
     *
     * @return array Updated configuration with pagination settings.
     */
    private function getNextPage(array $config, array $sourceConfig, int $currentPage): array
    {
        $config['pagination'] = [
            'paginationQuery' => ($sourceConfig['paginationQuery'] ?? 'page'),
            'page'            => $currentPage,
        ];

        return $config;

    }//end getNextPage()


    /**
     * Extracts the next API endpoint for pagination from the response body.
     *
     * @param array  $body The decoded JSON response body from the API.
     * @param string $url  The base URL of the API source.
     *
     * @return string|null The next endpoint URL if available, or null if there is no next page.
     */
    private function getNextEndpoint(array $body, string $url): ?string
    {
        $nextLink = $this->getNextlinkFromCall($body);

        if (str_starts_with($nextLink, $url)) {
            return substr($nextLink, strlen($url));
        }

        // Return nextLink if it doesn't start with base URL
        if ($nextLink !== null) {
            return $nextLink;
        }

        return null;

    }//end getNextEndpoint()


    /**
     * Retrieves the next link for pagination from the API response body.
     *
     * @param array $body The decoded JSON body of the API response.
     *
     * @return string|null The URL for the next page of results, or null if there is no next page.
     */
    public function getNextlinkFromCall(array $body): ?string
    {
        return $body['next'] ?? null;

    }//end getNextlinkFromCall()


    /**
     * Extracts all objects from the API response body.
     *
     * @param array           $array           The decoded JSON body of the API response.
     * @param Synchronization $synchronization The synchronization object containing source configuration.
     *
     * @return array An array of items extracted from the response body.
     * @throws Exception If the position of objects in the return body cannot be determined.
     */
    public function getAllObjectsFromArray(array $array, Synchronization $synchronization): array
    {
        // Get the source configuration from the synchronization object
        $sourceConfig = $synchronization->getSourceConfig();

        // Check if a specific objects position is defined in the source configuration
        if (empty($sourceConfig['resultsPosition']) === false) {
            $position = $sourceConfig['resultsPosition'];
            // if position is root, return the array
            if ($position === '_root' || $position === '_object') {
                return $array;
            }

            // Use Dot notation to access nested array elements
            $dot = new Dot($array);
            if ($dot->has($position) === true) {
                // Return the objects at the specified position
                return $dot->get($position);
            } else {
                // Throw an exception if the specified position doesn't exist       return [];
                // @todo log error
                // throw new Exception("Cannot find the specified position of objects in the return body.");
            }
        }

        // Define common keys to check for objects
        $commonKeys = [
            'items',
            'result',
            'results',
        ];

        // Loop through common keys and return first match found
        foreach ($commonKeys as $key) {
            if (isset($array[$key]) === true) {
                return $array[$key];
            }
        }

        // If no objects can be found, throw an exception
        throw new Exception("Cannot determine the position of objects in the return body.");

    }//end getAllObjectsFromArray()


    /**
     * Write an created, updated or deleted object to an external target.
     *
     * @param Synchronization         $synchronization The synchronization to run.
     * @param SynchronizationContract $contract        The contract to enforce.
     * @param string                  $endpoint        The endpoint to write the object to.
     *
     * @return SynchronizationContract The updated contract.
     *
     * @throws ContainerExceptionInterface
     * @throws GuzzleException
     * @throws LoaderError
     * @throws NotFoundExceptionInterface
     * @throws SyntaxError
     * @throws \OCP\DB\Exception
     */
    private function writeObjectToTarget(
        Synchronization $synchronization,
        SynchronizationContract $contract,
        string $endpoint,
    ): SynchronizationContract {
        $target = $this->sourceMapper->find(id: $synchronization->getTargetId());

        $sourceId = $synchronization->getSourceId();
        if ($synchronization->getSourceType() === 'register/schema' && $contract->getOriginId() !== null) {
            $sourceIds = explode(separator: '/', string: $sourceId);

            $this->objectService->getOpenRegisters()->setRegister($sourceIds[0]);
            $this->objectService->getOpenRegisters()->setSchema($sourceIds[1]);

            $object = $this->objectService->getOpenRegisters()->find(
                id: $contract->getOriginId(),
            )->jsonSerialize();
        }

        $targetConfig = $this->callService->applyConfigDot($synchronization->getTargetConfig());

        if (str_starts_with($endpoint, $target->getLocation()) === true) {
            $endpoint = str_replace(search: $target->getLocation(), replace: '', subject: $endpoint);
        }

        if ($contract->getOriginId() === null) {
            $endpoint .= '/'.$contract->getTargetId();
            $response  = $this->callService->call(source: $target, endpoint: $endpoint, method: 'DELETE', config: $targetConfig)->getResponse();

            $contract->setTargetHash(md5(serialize($response['body'])));
            $contract->setTargetId(null);

            return $contract;
        }

        // @TODO For now only JSON APIs are supported
        $targetConfig['json'] = $object;

        if ($contract->getTargetId() === null) {
            $response = $this->callService->call(source: $target, endpoint: $endpoint, method: 'POST', config: $targetConfig)->getResponse();

            $body = json_decode($response['body'], true);

            $contract->setTargetId(($body[$targetConfig['idposition']] ?? $body['id']));

            return $contract;
        }

        $endpoint .= '/'.$contract->getTargetId();

        $response = $this->callService->call(source: $target, endpoint: $endpoint, method: 'PUT', config: $targetConfig)->getResponse();

        $body = json_decode($response['body'], true);

        return $contract;

    }//end writeObjectToTarget()


    /**
     * Synchronize data to a target.
     *
     * The synchronizationContract should be given if the normal procedure to find the contract (on originId) is not available to the contract that should be updated.
     *
     * @param  ObjectEntity                 $object                  The object to synchronize
     * @param  SynchronizationContract|null $synchronizationContract If given: the synchronization contract that should be updated.
     * @param  bool|null                    $force                   If true, the object will be updated regardless of changes
     * @return array The updated synchronizationContracts
     *
     * @throws ContainerExceptionInterface
     * @throws LoaderError
     * @throws NotFoundExceptionInterface
     * @throws SyntaxError
     * @throws \OCP\DB\Exception
     * @throws GuzzleException
     */
    public function synchronizeToTarget(
        ObjectEntity $object,
        ?SynchronizationContract $synchronizationContract=null,
        ?bool $force=false,
        ?bool $test=false,
        ?SynchronizationLog $log=null
    ): array {
        $objectId = $object->getUuid();

        if ($synchronizationContract === null) {
            $synchronizationContract = $this->synchronizationContractMapper->findByOriginId($objectId);
        }

        $synchronizations = $this->synchronizationMapper->findAll(
            filters: [
                'source_type' => 'register/schema',
                'source_id'   => "{$object->getRegister()}/{$object->getSchema()}",
            ]
        );
        if (count($synchronizations) === 0) {
            return [];
        }

        $synchronization = $synchronizations[0];

        if ($synchronizationContract instanceof SynchronizationContract === false) {
            $synchronizationContract = $this->synchronizationContractMapper->createFromArray(
                [
                    'synchronizationId' => $synchronization->getId(),
                    'originId'          => $objectId,
                ]
            );
        }

        $synchronizationContract = $this->synchronizeContract(
            synchronizationContract: $synchronizationContract,
            synchronization: $synchronization,
            object: $object->jsonSerialize(),
            isTest: $test,
            force: $force,
            log: $log
        );

        if ($synchronizationContract instanceof SynchronizationContract === true) {
            // If this is a regular synchronizationContract update it to the database.
            $synchronizationContract = $this->synchronizationContractMapper->update(entity: $synchronizationContract);
        }

        $synchronizationContract = $this->synchronizationContractMapper->update($synchronizationContract);

        return [$synchronizationContract];

    }//end synchronizeToTarget()


    /**
     * Processes rules for an endpoint request
     *
     * @param Synchronization $synchronization The endpoint being processed
     * @param array           $data            Current request data
     * @param string          $timing
     * @param string|null     $objectId
     * @param int|null        $registerId
     * @param int|null        $schemaId
     *
     * @return array|JSONResponse Returns modified data or error response if rule fails
     * @throws ContainerExceptionInterface
     * @throws GuzzleException
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function processRules(Synchronization $synchronization, array $data, string $timing, ?string $objectId=null, ?int $registerId=null, ?int $schemaId=null): (array | JSONResponse)
    {
        $rules = $synchronization->getActions();
        if (empty($rules) === true) {
            return $data;
        }

        try {
            // Get all rules at once and sort by order
            $ruleEntities = array_filter(
                array_map(
                    fn($ruleId) => $this->getRuleById($ruleId),
                    $rules
                )
            );

            // Sort rules by order
            usort($ruleEntities, fn($a, $b) => ($a->getOrder() - $b->getOrder()));

            // Process each rule in order
            foreach ($ruleEntities as $rule) {
                // Check rule conditions
                if ($this->checkRuleConditions($rule, $data) === false || $rule->getTiming() !== $timing) {
                    continue;
                }

                // Process rule based on type
                $result = match ($rule->getType()) {
                    'error' => $this->processErrorRule($rule),
                    'mapping' => $this->processMappingRule($rule, $data),
                    'synchronization' => $this->processSyncRule($rule, $data),
                    'fetch_file' => $this->processFetchFileRule($rule, $data, $objectId),
                    'write_file' => $this->processWriteFileRule($rule, $data, $objectId, $registerId, $schemaId),
                default => throw new Exception('Unsupported rule type: '.$rule->getType()),
                };

                    // If result is JSONResponse, return error immediately
                    if ($result instanceof JSONResponse) {
                        return $result;
                    }

                    // Update data with rule result
                    $data = $result;
            }//end foreach

            return $data;
        } catch (Exception $e) {
            // $this->logger->error('Error processing rules: ' . $e->getMessage());
            return new JSONResponse(['error' => 'Rule processing failed: '.$e->getMessage()], 500);
        }//end try

    }//end processRules()


    /**
     * Get a rule by its ID using RuleMapper
     *
     * @param string $id The unique identifier of the rule
     *
     * @return Rule|null The rule object if found, or null if not found
     */
    private function getRuleById(string $id): ?Rule
    {
        try {
            return $this->ruleMapper->find((int) $id);
        } catch (Exception $e) {
            // $this->logger->error('Error fetching rule: ' . $e->getMessage());
            return null;
        }

    }//end getRuleById()


    /**
     * Write a file to the filesystem
     *
     * @param string $fileName The filename
     * @param string $content  The file content
     * @param string $objectId The object ID
     *
     * @return File|bool The created file or false on failure
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws GenericFileException
     * @throws LockedException
     */
    private function writeFile(
        string $fileName,
        string $content,
        string $objectId
    ): mixed {
        $object = $this->objectService->getOpenRegisters()
            ->getMapper('objectEntity')
            ->find($objectId);

        try {
            $file = $this->storageService->writeFile(
                path: $object->getFolder(),
                fileName: $fileName,
                content: $content
            );
        } catch (NotFoundException | NotPermittedException | NoUserException $e) {
            return false;
        }

        return $file;

    }//end writeFile()


    /**
     * Fetch a file from a source
     *
     * @param Source              $source   The source configuration
     * @param string              $endpoint The file endpoint
     * @param array<string,mixed> $config   The action configuration
     * @param string              $objectId The object ID
     * @param array<string>|null  $tags     Optional tags to assign
     * @param string|null         $filename Optional filename
     *
     * @return string The file URL or base64 content
     *
     * @throws ContainerExceptionInterface
     * @throws GenericFileException
     * @throws GuzzleException
     * @throws LoaderError
     * @throws LockedException
     * @throws NotFoundExceptionInterface
     * @throws SyntaxError
     * @throws \OCP\DB\Exception
     */
    private function fetchFile(
        Source $source,
        string $endpoint,
        array $config,
        string $objectId,
        ?array $tags=[],
        ?string $filename=null
    ): string {
        $originalEndpoint = $endpoint;

        // Clean endpoint if it contains source location
        $endpoint = str_contains(haystack: $endpoint, needle: $source->getLocation()) === true ? substr(string : $endpoint, offset: strlen(string: $source->getLocation()))
            : $endpoint;

        // Make API call
        $result   = $this->callService->call(
            source: $source,
            endpoint: $endpoint,
            method: ($config['method'] ?? 'GET'),
            config: ($config['sourceConfiguration'] ?? [])
        );
        $response = $result->getResponse();

        // Return base64 content if write is disabled
        if (isset($config['write']) === true && $config['write'] === false) {
            return base64_encode($response['body']);
        }

        // Get filename from response if not provided
        if ($filename === null) {
            $filename = $this->getFilenameFromHeaders(
                response: $response,
                result: $result
            );
        }

        // Write file using ObjectService
        $objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');
        $file          = $objectService->addFile(
            object: $objectId,
            fileName: $filename,
            base64Content: $response['body'],
            share: isset($config['autoShare']) ? $config['autoShare'] : false
        );

        // Attach tags to file
        $tags[] = "object:$objectId";
        if ($file instanceof File === true && isset($tags) === true && empty($tags) === false) {
            $this->attachTagsToFile(fileId: $file->getId(), tags: $tags);
        }

        return $originalEndpoint;

    }//end fetchFile()


    /**
     * Extract filename from response headers
     *
     * @param array<string,mixed> $response The response data
     * @param CallLog             $result   The call log
     *
     * @return string|null The extracted filename
     */
    private function getFilenameFromHeaders(array $response, CallLog $result): ?string
    {
        // Try Content-Disposition header first
        if (isset($response['headers']['Content-Disposition']) === true
            && str_contains($response['headers']['Content-Disposition'][0], 'filename')
        ) {
            $explodedContentDisposition = explode(
                '=',
                $response['headers']['Content-Disposition'][0]
            );
            return trim(string: $explodedContentDisposition[1], characters: '"');
        }

        // Fall back to URL path and content type
        $parsedUrl = parse_url($result->getRequest()['url']);
        $path      = explode(separator: '/', string: $parsedUrl['path']);
        $filename  = end($path);

        // Add extension from content type if missing
        if (count(explode(separator: '.', string: $filename)) === 1
            && (isset($response['headers']['Content-Type']) === true
            || isset($response['headers']['content-type']) === true)
        ) {
            $contentType = isset($response['headers']['Content-Type']) === true ? $response['headers']['Content-Type'][0] : $response['headers']['content-type'][0];

            $explodedMimeType = explode(
                separator: '/',
                string: explode(separator: ';', string: $contentType)[0]
            );

            $filename = $filename.'.'.end($explodedMimeType);
        }

        return $filename;

    }//end getFilenameFromHeaders()


    /**
     * Process a rule to fetch a file from external source
     *
     * @param Rule                $rule     The rule to process
     * @param array<string,mixed> $data     The object data
     * @param string              $objectId The object ID
     *
     * @return array<string,mixed> The updated object data
     *
     * @throws ContainerExceptionInterface
     * @throws GenericFileException
     * @throws GuzzleException
     * @throws LoaderError
     * @throws LockedException
     * @throws NotFoundExceptionInterface
     * @throws SyntaxError
     * @throws \OCP\DB\Exception
     * @throws Exception
     */
    private function processFetchFileRule(
        Rule $rule,
        array $data,
        string $objectId
    ): array {
        // Validate configuration
        if (isset($rule->getConfiguration()['fetch_file']) === false) {
            throw new Exception('No configuration found for fetch_file');
        }

        $config = $rule->getConfiguration()['fetch_file'];
        $source = $this->sourceMapper->find($config['source']);

        // Get file path from data
        $dataDot  = new Dot($data);
        $endpoint = $dataDot[$config['filePath']];

        if ($endpoint === null) {
            return $dataDot->jsonSerialize();
        }

        // Process single or multiple endpoints
        if (is_array($endpoint) === true) {
            $result = [];
            foreach ($endpoint as $key => $value) {
                // Extract tags and filename
                $tags     = [];
                $filename = null;

                if (is_array($value) === true) {
                    $endpoint = $value['endpoint'];

                    // Add label as tag if configured
                    if (isset($value['label']) === true
                        && isset($config['tags']) === true
                        && in_array(needle: $value['label'], haystack: $config['tags']) === true
                    ) {
                        $tags = [$value['label']];
                    }

                    if (isset($value['filename']) === true) {
                        $filename = $value['filename'];
                    }
                } else {
                    $endpoint = $value;
                }

                $result[$key] = $this->fetchFile(
                    source: $source,
                    endpoint: $endpoint,
                    config: $config,
                    objectId: $objectId,
                    tags: $tags,
                    filename: $filename
                );
            }//end foreach

            $dataDot[$config['filePath']] = $result;
        } else {
            $dataDot[$config['filePath']] = $this->fetchFile(
                source: $source,
                endpoint: $endpoint,
                config: $config,
                objectId: $objectId
            );
        }//end if

        return $dataDot->jsonSerialize();

    }//end processFetchFileRule()


    /**
     * Attach tags to a file
     *
     * @param string        $fileId The file ID
     * @param array<string> $tags   The tags to attach
     *
     * @return void
     */
    private function attachTagsToFile(string $fileId, array $tags): void
    {
        $tagIds = [];
        foreach ($tags as $key => $tagName) {
            try {
                $tag = $this->systemTagManager->getTag(tagName: $tagName, userVisible: true, userAssignable: true);
            } catch (TagNotFoundException $exception) {
                $tag = $this->systemTagManager->createTag(tagName: $tagName, userVisible: true, userAssignable: true);
            }

            $tagIds[] = $tag->getId();
        }

        $this->systemTagMapper->assignTags(objId: $fileId, objectType: $this::FILE_TAG_TYPE, tagIds: $tagIds);

    }//end attachTagsToFile()


    /**
     * Process a rule to write files.
     *
     * @param Rule   $rule       The rule to process.
     * @param array  $data       The data to write.
     * @param string $objectId   The object to write the data to.
     * @param int    $registerId The register the object is in.
     * @param int    $schemaId   The schema the object is in.
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function processWriteFileRule(Rule $rule, array $data, string $objectId, int $registerId, int $schemaId): array
    {
        if (isset($rule->getConfiguration()['write_file']) === false) {
            throw new Exception('No configuration found for write_file');
        }

        $config  = $rule->getConfiguration()['write_file'];
        $dataDot = new Dot($data);
        $files   = $dataDot[$config['filePath']];
        if (isset($files) === false || empty($files) === true) {
            return $dataDot->jsonSerialize();
        }

        // Check if associative array
        if (is_array($files) === true && isset($files[0]) === true & array_keys($files[0]) !== range(0, (count($files[0]) - 1))) {
            $result = [];
            foreach ($files as $key => $value) {
                // Check for tags
                $tags = [];
                if (is_array($value) === true) {
                    $content = $value['content'];
                    if (isset($value['label']) === true && isset($config['tags']) === true
                        && in_array(needle: $value['label'], haystack: $config['tags']) === true
                    ) {
                        $tags = [$value['label']];
                    }

                    if (isset($value['filename']) === true) {
                        $fileName = $value['filename'];
                    }
                } else {
                    $content = $value;
                }

                $openRegisters = $this->objectService->getOpenRegisters();
                $openRegisters->setRegister($registerId);
                $openRegisters->setSchema($schemaId);

                try {
                    // Write file with OpenRegister ObjectService.
                    $objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');
                    $file          = $objectService->addFile(object: $objectId, fileName: $fileName, base64Content: $content);

                    $tags = array_merge(($config['tags'] ?? []), ["object:$objectId"]);
                    if ($file instanceof File === true) {
                        $this->attachTagsToFile(fileId: $file->getId(), tags: $tags);
                    }

                    $result[$key] = $file->getPath();
                } catch (Exception $exception) {
                }
            }//end foreach

            $result[$key]                 = $file->getPath();
            $dataDot[$config['filePath']] = $result;
        } else {
            $content       = $files;
            $fileName      = $dataDot[$config['fileNamePath']];
            $openRegisters = $this->objectService->getOpenRegisters();
            $openRegisters->setRegister($registerId);
            $openRegisters->setSchema($schemaId);

            try {
                // Write file with OpenRegister ObjectService.
                $objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');
                $file          = $objectService->addFile(object: $objectId, fileName: $fileName, base64Content: $content);

                $tags = array_merge(($config['tags'] ?? []), ["object:$objectId"]);
                if ($file instanceof File === true) {
                    $this->attachTagsToFile(fileId: $file->getId(), tags: $tags);
                }

                $dataDot[$config['filePath']] = $file->getPath();
            } catch (Exception $exception) {
            }
        }//end if

        return $dataDot->jsonSerialize();

    }//end processWriteFileRule()


    /**
     * Process an error rule and return an error response
     *
     * @param Rule $rule The rule containing error configuration
     *
     * @return JSONResponse The error response with details and status code
     *
     * @psalm-return JSONResponse<array{error: string, message: string}>
     */
    private function processErrorRule(Rule $rule): JSONResponse
    {
        $config = $rule->getConfiguration();
        return new JSONResponse(
            [
                'error'   => $config['error']['name'],
                'message' => $config['error']['message'],
            ],
            $config['error']['code']
        );

    }//end processErrorRule()


    /**
     * Process a mapping rule to transform data
     *
     * @param Rule                $rule The rule containing mapping configuration
     * @param array<string,mixed> $data The data to transform
     *
     * @return array<string,mixed> The transformed data
     *
     * @throws DoesNotExistException When mapping configuration is missing
     * @throws MultipleObjectsReturnedException When multiple mapping objects found
     * @throws LoaderError When mapping template fails to load
     * @throws SyntaxError When mapping syntax is invalid
     */
    private function processMappingRule(Rule $rule, array $data): array
    {
        $config  = $rule->getConfiguration();
        $mapping = $this->mappingService->getMapping($config['mapping']);

        return $this->mappingService->executeMapping($mapping, $data);

    }//end processMappingRule()


    /**
     * Process a synchronization rule
     *
     * @param Rule                $rule The rule containing sync configuration
     * @param array<string,mixed> $data The data to synchronize
     *
     * @return array<string,mixed> The synchronized data
     */
    private function processSyncRule(Rule $rule, array $data): array
    {
        $config = $rule->getConfiguration();
        // Here you would implement the synchronization logic
        // For now, just return the data unchanged
        return $data;

    }//end processSyncRule()


    /**
     * Check if rule conditions are met for given data
     *
     * @param Rule                $rule The rule containing conditions
     * @param array<string,mixed> $data The data to evaluate conditions against
     *
     * @return bool True if conditions are met, false otherwise
     *
     * @throws Exception When condition evaluation fails
     */
    private function checkRuleConditions(Rule $rule, array $data): bool
    {
        $conditions = $rule->getConditions();

        // Return true if no conditions specified
        if (empty($conditions) === true) {
            return true;
        }

        return JsonLogic::apply($conditions, $data) === true;

    }//end checkRuleConditions()


    /**
     * Replace characters in array keys recursively
     *
     * @param array<string,mixed> $array       The array to process
     * @param string              $toReplace   The character to replace
     * @param string              $replacement The replacement character
     *
     * @return array<string,mixed> The array with replaced key characters
     */
    public function encodeArrayKeys(
        array $array,
        string $toReplace,
        string $replacement
    ): array {
        $result = [];

        foreach ($array as $key => $value) {
            // Replace character in key
            $newKey = str_replace($toReplace, $replacement, $key);

            // Recursively process nested arrays
            if (is_array($value) === true && $value !== []) {
                $result[$newKey] = $this->encodeArrayKeys(
                    array: $value,
                    toReplace: $toReplace,
                    replacement: $replacement
                );
                continue;
            }

            $result[$newKey] = $value;
        }

        return $result;

    }//end encodeArrayKeys()


    /**
     * Convert SimpleXMLElement to array preserving namespaces
     *
     * @param SimpleXMLElement $xml The XML element to convert
     *
     * @return array<string,mixed> The array representation with preserved namespaces
     *
     * @psalm-suppress MixedPropertyFetch
     * @psalm-suppress MixedMethodCall
     */
    private function xmlToArray(SimpleXMLElement $xml): array
    {
        $result = [];

        // Handle regular attributes
        $attributes = $xml->attributes();
        if (count($attributes) > 0) {
            $result['@attributes'] = [];
            foreach ($attributes as $attrName => $attrValue) {
                $result['@attributes'][(string) $attrName] = (string) $attrValue;
            }
        }

        // Handle namespaced attributes
        $namespaces = $xml->getNamespaces(true);
        foreach ($namespaces as $prefix => $namespace) {
            $nsAttributes = $xml->attributes($namespace);
            if (count($nsAttributes) > 0) {
                if (!isset($result['@attributes'])) {
                    $result['@attributes'] = [];
                }

                foreach ($nsAttributes as $attrName => $attrValue) {
                    // Preserve the namespace prefix in the attribute name (with colon)
                    $nsAttrName                         = $prefix ? "$prefix:$attrName" : $attrName;
                    $result['@attributes'][$nsAttrName] = (string) $attrValue;
                }
            }
        }

        // Handle child elements
        foreach ($xml->children() as $childName => $child) {
            $childArray = $this->xmlToArray($child);

            if (isset($result[$childName])) {
                // If this child name already exists, convert to or add to array
                if (!is_array($result[$childName]) || !isset($result[$childName][0])) {
                    $result[$childName] = [$result[$childName]];
                }

                $result[$childName][] = $childArray;
            } else {
                $result[$childName] = $childArray;
            }
        }

        // Handle text content
        $text = trim((string) $xml);
        if (count($result) === 0 && $text !== '') {
            return ['#text' => $text];
        } else if ($text !== '') {
            $result['#text'] = $text;
        }

        return $result;

    }//end xmlToArray()


    /**
     * Process a single object during synchronization
     *
     * @param Synchronization     $synchronization The synchronization being processed
     * @param array<string,mixed> $object          The object to synchronize
     * @param array<string,mixed> $result          The current result tracking data
     * @param bool                $isTest          Whether this is a test run
     * @param bool                $force           Whether to force synchronization regardless of changes
     * @param SynchronizationLog  $log             The synchronization log
     *
     * @return array{result: array<string,mixed>, targetId: string|null} The updated result data and target ID
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function processSynchronizationObject(
        Synchronization $synchronization,
        array $object,
        array $result,
        bool $isTest,
        bool $force,
        SynchronizationLog $log
    ): array {
        // Skip non-array objects
        if (is_array($object) === false) {
            $result['objects']['invalid']++;
            return [
                'result'   => $result,
                'targetId' => null,
            ];
        }

        // Encode array keys to handle dots
        $conditionsObject = $this->encodeArrayKeys($object, '.', '&#46;');

        // Check if object meets synchronization conditions
        if ($synchronization->getConditions() !== []
            && !JsonLogic::apply($synchronization->getConditions(), $conditionsObject)
        ) {
            $result['objects']['skipped']++;
            return [
                'result'   => $result,
                'targetId' => null,
            ];
        }

        // Extract origin ID from source object
        $originId = $this->getOriginId($synchronization, $object);

        // Get or create synchronization contract
        $synchronizationContract = $this->synchronizationContractMapper->findSyncContractByOriginId(
            synchronizationId: $synchronization->id,
            originId: $originId
        );

        // Process new contract
        if ($synchronizationContract instanceof SynchronizationContract === false) {
            $synchronizationContract = new SynchronizationContract();
            $synchronizationContract->setSynchronizationId($synchronization->getId());
            $synchronizationContract->setOriginId($originId);

            $synchronizationContractResult = $this->synchronizeContract(
                synchronizationContract: $synchronizationContract,
                synchronization: $synchronization,
                object: $object,
                isTest: $isTest,
                force: $force,
                log: $log
            );

            $synchronizationContract = $synchronizationContractResult['contract'];
            $result['contracts'][]   = isset($synchronizationContractResult['contract']['uuid']) ? $synchronizationContractResult['contract']['uuid'] : null;
            $result['logs'][]        = isset($synchronizationContractResult['log']['uuid']) ? $synchronizationContractResult['log']['uuid'] : null;
            $result['objects']['created']++;
        }//end if
        // Process existing contract
        else {
            $synchronizationContractResult = $this->synchronizeContract(
                synchronizationContract: $synchronizationContract,
                synchronization: $synchronization,
                object: $object,
                isTest: $isTest,
                force: $force,
                log: $log
            );

            $synchronizationContract = $synchronizationContractResult['contract'];
            $result['contracts'][]   = isset($synchronizationContractResult['contract']['uuid']) ? $synchronizationContractResult['contract']['uuid'] : null;
            $result['logs'][]        = isset($synchronizationContractResult['log']['uuid']) ? $synchronizationContractResult['log']['uuid'] : null;
            $result['objects']['updated']++;
        }

        $targetId = $synchronizationContract['targetId'] ?? null;

        return [
            'result'   => $result,
            'targetId' => $targetId,
        ];

    }//end processSynchronizationObject()


    /**
     * Fetch a synchronization by ID or other characteristics
     *
     * @param string|int|null     $id      The synchronization ID
     * @param array<string,mixed> $filters Additional filters to find the synchronization
     *
     * @return Synchronization The found synchronization
     *
     * @throws DoesNotExistException When synchronization not found
     * @throws InvalidArgumentException When invalid ID type provided
     */
    public function getSynchronization(
        null ( | string | int $id)=null,
        array $filters=[]
    ): Synchronization {
        // Find by ID if provided
        if ($id !== null) {
            return $this->synchronizationMapper->find(intval($id));
        }

        // Find by filters
        /*
         * @var Synchronization[] $synchronizations
         */
        $synchronizations = $this->synchronizationMapper->findAll(filters: $filters);

        if ($synchronizations === 0) {
            throw new DoesNotExistException('The synchronization you are looking for does not exist');
        }

        return $synchronizations[0];

    }//end getSynchronization()


}//end class
