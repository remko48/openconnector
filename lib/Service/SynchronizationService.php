<?php

namespace OCA\OpenConnector\Service;

use Exception;
use OCA\OpenConnector\Db\CallLog;
use OCA\OpenConnector\Db\Source;
use OCA\OpenConnector\Db\SourceMapper;
use OCA\OpenConnector\Db\MappignMapper;
use OCA\OpenConnector\Db\Synchronization;
use OCA\OpenConnector\Db\SynchronizationMapper;
use OCA\OpenConnector\Db\SynchronizationContract;
use OCA\OpenConnector\Db\SynchronizationContractLog;
use OCA\OpenConnector\Db\SynchronizationContractLogMapper;
use OCA\OpenConnector\Db\SynchronizationContractMapper;
use OCA\OpenConnector\Service\CallService;
use OCA\OpenConnector\Service\MappingService;
use Symfony\Component\Uid\Uuid;
use OCP\AppFramework\Db\DoesNotExistException;
use Adbar\Dot;

use Psr\Container\ContainerInterface;
use DateInterval;
use DateTime;
use OCA\OpenConnector\Db\MappingMapper;
use OCP\AppFramework\Http\NotFoundResponse;

class SynchronizationService
{
    private CallService $callService;
    private MappingService $mappingService;
    private ContainerInterface $containerInterface;
    private SynchronizationMapper $synchronizationMapper;
    private SourceMapper $sourceMapper;
    private MappingMapper $mappingMapper;
    private SynchronizationContractMapper $synchronizationContractMapper;
    private SynchronizationContractLogMapper $synchronizationContractLogMapper;
    private ObjectService $objectService;
    private Source $source;


	public function __construct(
		CallService $callService,
		MappingService $mappingService,
		ContainerInterface $containerInterface,
        SourceMapper $sourceMapper,
        MappingMapper $mappingMapper,
		SynchronizationMapper $synchronizationMapper,
		SynchronizationContractMapper $synchronizationContractMapper,
        SynchronizationContractLogMapper $synchronizationContractLogMapper
	) {
		$this->callService = $callService;
		$this->mappingService = $mappingService;
		$this->containerInterface = $containerInterface;
		$this->synchronizationMapper = $synchronizationMapper;
		$this->mappingMapper = $mappingMapper;
		$this->synchronizationContractMapper = $synchronizationContractMapper;
        $this->synchronizationContractLogMapper = $synchronizationContractLogMapper;
        $this->sourceMapper = $sourceMapper;
	}

	/**
	 * Synchronizes a given synchronization (or a complete source).
	 *
	 * @param Synchronization $synchronization
	 * @param bool|null       $isTest False by default, currently added for synchronziation-test endpoint
     * 
	 * @throws Exception
     *
	 * @return array
	 */
    public function synchronize(Synchronization $synchronization, ?bool $isTest = false): array
	{
        $objectList = $this->getAllObjectsFromSource(synchronization: $synchronization, isTest: $isTest);

        foreach ($objectList as $key => $object) {
            
            // Check for origin id.
            $originId = $this->getOriginId($synchronization, $object);
            
            // Get the synchronization contract for this object
            $synchronizationContract = $this->synchronizationContractMapper->findSynchronizationContractWithOriginId(synchronizationId: $synchronization->id, originId: $originId);

            if ($synchronizationContract instanceof SynchronizationContract === false) {
                // Only persist if not test
                if ($isTest === false) {
                    $synchronizationContract = $this->synchronizationContractMapper->createFromArray([
                        'synchronizationId' => $synchronization->getId(),
                        'originId' => $originId,
                        'originHash' => md5(serialize($object))
                    ]);
                } else {
                    $synchronizationContract = new SynchronizationContract();
                    $synchronizationContract->setSynchronizationId($synchronization->getId());
                    $synchronizationContract->setOriginId($originId);
                    $synchronizationContract->setOriginHash(md5(serialize($object)));
                }

                $synchronizationContract = $this->synchronizeContract(synchronizationContract: $synchronizationContract, synchronization: $synchronization, object: $object, isTest: $isTest);

                if ($isTest === true && is_array($synchronizationContract) === true) {
                    // If this is a log and contract array return for the test endpoint.
                    $logAndContractArray = $synchronizationContract;

                    return $logAndContractArray;
                }
            } else {
                // @todo this is wierd
                $synchronizationContract = $this->synchronizeContract(synchronizationContract: $synchronizationContract, synchronization: $synchronization, object: $object, isTest: $isTest);
                if ($isTest === false && $synchronizationContract instanceof SynchronizationContract === true) {
                    // If this is a regular synchronizationContract update it to the database.
                    $objectList[$key] = $this->synchronizationContractMapper->update(entity: $synchronizationContract);
                } elseif ($isTest === true && is_array($synchronizationContract) === true) {
                    // If this is a log and contract array return for the test endpoint.
                    $logAndContractArray = $synchronizationContract;
                    return $logAndContractArray;
                }
            }

            $this->synchronizationContractMapper->update($synchronizationContract);
        }

        return $objectList;
    }

    /**
     * Gets id from object as is in the origin
     * 
     * @param Synchronization $synchronization
     * @param array $object
     * 
     * @return string|int id
     */
    private function getOriginId(Synchronization $synchronization, array $object)
    {
        $originIdLocation = 'id';
        if ($synchronization->getOriginIdLocation() !== null) {
            $originIdLocation = $synchronization->getOriginIdLocation();
        }
        $objectDot = new Dot($object);
        $originId = $objectDot->get($originIdLocation);
        if ($originId === null) {
            throw new Exception('Could not find origin id in object for key: ' . $originIdLocation);
        }

        return $originId;
    }

	/**
	 * Synchronize a contract
     *
	 * @param SynchronizationContract $synchronizationContract
	 * @param Synchronization|null    $synchronization
	 * @param array $object
	 * @param bool|null               $isTest False by default, currently added for synchronziation-test endpoint
     *
	 * @throws Exception
	 *
	 * @return SynchronizationContract
	 */
    public function synchronizeContract(SynchronizationContract $synchronizationContract, Synchronization $synchronization = null, array $object = [], ?bool $isTest = false)
    {
        // Let create a source hash for the object
        $originHash = md5(serialize($object));
        $synchronizationContract->setSourceLastChecked(new DateTime());

        // Let's prevent pointless updates @todo account for omnidirectional sync, unless the config has been updated since last check then we do want to rebuild and check if the tagert object has changed
        if ($originHash === $synchronizationContract->getOriginHash() && $synchronization->getUpdated() < $synchronizationContract->getSourceLastChecked()) {
            // The object has not changed and the config has not been updated since last check
            // return $synchronizationContract;
            // @todo: somehow this always returns true, so we never do the updateTarget
        }

        // The object has changed, oke let do mappig and bla die bla
        $synchronizationContract->setOriginHash($originHash);
        $synchronizationContract->setSourceLastChanged(new DateTime());

        try {
            $sourceTargetMapping = $this->mappingMapper->find(id: $synchronization->getSourceTargetMapping());
        } catch (DoesNotExistException $exception) {
            return new Exception($exception->getMessage());
        }

        // let do the mapping if provided
        if ($sourceTargetMapping) {
            $targetObject = $this->mappingService->executeMapping(mapping: $sourceTargetMapping, input: $object);
        } else {
            $targetObject = $object;
        }


        // set the target hash
        $targetHash = md5(serialize($targetObject));
        $synchronizationContract->setTargetHash($targetHash);
        $synchronizationContract->setTargetLastChanged(new DateTime());
        $synchronizationContract->setTargetLastSynced(new DateTime());
        $synchronizationContract->setSourceLastSynced(new DateTime());

        // Do the magic!!
        if ($isTest === false) {
            $synchronizationContract = $this->updateTarget(synchronizationContract: $synchronizationContract, targetObject: $targetObject);
        }

        // Log it
        if ($isTest === false) {
            $log = $this->synchronizationContractLogMapper->createFromArray([
                'synchronizationId' => $synchronizationContract->getSynchronizationId(),
                'synchronizationContractId' => $synchronizationContract->getId(),
                'source' => $object,
                'target' => $targetObject,
                'expires' => new DateTime('+1 day')
            ]);
        }

        if ($isTest === true) {
            return ['log' => $log->jsonSerialize(), 'contract' => $synchronizationContract->jsonSerialize()];
        }

        return $synchronizationContract;

    }

	/**
	 * Write the data to the target
	 *
	 * @param SynchronizationContract $synchronizationContract
	 * @param array                   $targetObject
     *
	 * @throws Exception
	 *
	 * @return SynchronizationContract
	 */
    public function updateTarget(SynchronizationContract $synchronizationContract, array $targetObject): SynchronizationContract
	{
         // The function can be called solo set let's make sure we have the full synchronization object
        if (isset($synchronization) === false) {
            $synchronization = $this->synchronizationMapper->find($synchronizationContract->getSynchronizationId());
        }

        // Let's check if we need to create or update
        $update = false;
        if ($synchronizationContract->getTargetId()){
            $update = true;
        }

        $type = $synchronization->getTargetType();

        switch ($type) {
            case 'register/schema':
                // Setup the object service
                $objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');

                // if we already have an id, we need to get the object and update it
                if ($synchronizationContract->getTargetId() !== null) {
                    $targetObject['id'] = $synchronizationContract->getTargetId();
                }

                // Extract register and schema from the targetId
                // The targetId needs to be filled in as: {registerId} + / + {schemaId} for example: 1/1
                $targetId = $synchronization->getTargetId();
                list($register, $schema) = explode('/', $targetId);

                // Save the object to the target
                $target = $objectService->saveObject($register, $schema, $targetObject);

                // Get the id form the target object
                $synchronizationContract->setTargetId($target->getUuid());
                break;
            case 'api':
                //@todo: implement
                //$this->callService->put($targetObject);
                break;
            case 'database':
                //@todo: implement
                break;
            default:
                throw new Exception("Unsupported target type: $type");
        }

        return $synchronizationContract;
    }

    /**
     * Get all the object from a source
     *
     * @param Synchronization $synchronization
	 * @param bool|null       $isTest False by default, currently added for synchronziation-test endpoint
     *
     * @return array
     */
    public function getAllObjectsFromSource(Synchronization $synchronization, ?bool $isTest = false)
    {
        $objects = [];

        $type = $synchronization->getSourceType();

        switch ($type) {
            case 'register/schema':
                // Setup the object service
                $this->objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');

                break;
            case 'api':
                $objects = $this->getAllObjectsFromApi(synchronization: $synchronization, isTest: $isTest);
                break;
            case 'database':
                //@todo: implement
                break;
        }

        return $objects;
    }

    /**
     * Retrieves all objects from an API source for a given synchronization.
     *
     * @param Synchronization $synchronization The synchronization object containing source information.
     * @param bool            $isTest          If we only want to return a single object (for example a test)
     *
     * @return array An array of all objects retrieved from the API.
     */
    public function getAllObjectsFromApi(Synchronization $synchronization, ?bool $isTest = false)
    {
        $objects = [];
        $source = $this->sourceMapper->find($synchronization->getSourceId());

        // Make the initial API call
        $response = $this->callService->call($source)->getResponse();
        $body = json_decode($response['body'], true);
        $objects = array_merge($objects, $this->getAllObjectsFromArray($body, $synchronization));

        // Return a single object or empty array if in test mode
        if ($isTest === true) {
            return [$objects[0]] ?? [];
        }

        // Current page is 2 because the first call made above is page 1.
        $currentPage = 2;

        // Continue making API calls if there are more pages from 'next' the response body or if paginationQuery is set
        while ($endpoint = $this->getNextEndpoint($body, $source, $synchronization, $currentPage)) {
            $response = $this->callService->call($source, $endpoint)->getResponse();
            $body = json_decode($response['body'], true);

            if (empty($body) === true) {
                return $objects;
            }

            $objects = array_merge($objects, $this->getAllObjectsFromArray($body, $synchronization));

            $currentPage++;
        }

        return $objects;
    }

    /**
     * Determines the next API endpoint based on either a provided next link or a pagination query.
     *
     * @param array           $body
     * @param mixed           $source
     * @param Synchronization $synchronization Synchronization object to retrieve next link details.
     * @param int      $currentPage The current page number for pagination, used if no next link is available.
     *
     * @return string|null The next endpoint URL if a next link or pagination query is available, or null if neither exists.
     */
    private function getNextEndpoint(array $body, $source, Synchronization $synchronization, int $currentPage): ?string
    {
        $nextLink = $this->getNextlinkFromCall($body, $synchronization);

        if ($nextLink) {
            return str_replace($source->getLocation(), '', $nextLink);
        }

        // If paginationQuery exists, replace any placeholder with the current page number
        $paginationQuery = $source->getPaginationConfig()['queryParam'] ?? 'page';

        return "{$source->getLocation()}?$paginationQuery=$currentPage";

        return null;
    }

    /**
     * Extracts all objects from the API response body.
     *
     * @param array $body The decoded JSON body of the API response.
     * @param Synchronization $synchronization The synchronization object containing source configuration.
     *
     * @throws Exception If the position of objects in the return body cannot be determined.
     *
     * @return array An array of items extracted from the response body.
     */
    public function getAllObjectsFromArray(array $array, Synchronization $synchronization)
    {
        // Get the source configuration from the synchronization object
        $sourceConfig = $synchronization->getSourceConfig();

        // Check if a specific objects position is defined in the source configuration
        if (isset($sourceConfig['objectsPosition']) === true) {
            $position = $sourceConfig['objectsPosition'];
            // Use Dot notation to access nested array elements
            $dot = new Dot($array);
            if ($dot->has($position)) {
                // Return the objects at the specified position
                return $dot->get($position);
            } else {
                // Throw an exception if the specified position doesn't exist
                throw new Exception("Cannot find the specified position of objects in the return body.");
            }
        }

        // Check for common keys where objects might be stored
        // If 'items' key exists, return its value
        if (isset($array['items']) === true) {
            return $array['items'];
        }

        // If 'result' key exists, return its value
        if (isset($array['result']) === true) {
            return $array['result'];
        }

        // If 'results' key exists, return its value
        if (isset($array['results']) === true) {
            return $array['results'];
        }

        // If no objects can be found, throw an exception
        throw new Exception("Cannot determine the position of objects in the return body.");
    }

    /**
     * Retrieves the next link for pagination from the API response body.
     *
     * @param array $body The decoded JSON body of the API response.
     * @param Synchronization $synchronization The synchronization object (unused in this method, but kept for consistency).
     *
     * @return string|bool The URL for the next page of results, or false if there is no next page.
     */
    public function getNextlinkFromCall(array $body, Synchronization $synchronization): string | bool | null
    {
        // Check if the 'next' key exists in the response body
        return $body['next'];
    }
}
