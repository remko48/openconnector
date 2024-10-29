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
	 * @return array
	 * @throws Exception
	 */
    public function synchronize(Synchronization $synchronization, ?bool $isTest = false): array
	{
        $objectList = $this->getAllObjectsFromSource(synchronization: $synchronization, isTest: $isTest);

        foreach ($objectList as $key => $object) {
            // Get the synchronization contract for this object
            $synchronizationContract = $this->synchronizationContractMapper->findOnSynchronizationIdSourceId(synchronizationId: $synchronization->id, sourceId: $object['id']);

            if ($synchronizationContract instanceof SynchronizationContract === false) {
                $synchronizationContract = new SynchronizationContract();
                $synchronizationContract->setUuid(Uuid::v4());
                $synchronizationContract->setSynchronizationId($synchronization->id);
                $synchronizationContract->setSourceId($object['id']);
                $synchronizationContract->setSourceHash(md5(serialize($object)));

                $synchronizationContract = $this->synchronizeContract(synchronizationContract: $synchronizationContract, synchronization: $synchronization, object: $object, isTest: $isTest);

                if ($isTest === false && $synchronizationContract instanceof SynchronizationContract === true) {
                    // If this is a regular synchronizationContract create it to the database.
                    $objectList[$key] = $this->synchronizationContractMapper->insert(entity: $synchronizationContract);
                } elseif ($isTest === true && is_array($synchronizationContract) === true) {
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
        }

        return $objectList;
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
        $sourceHash = md5(serialize($object));
        $synchronizationContract->setSourceLastChecked(new DateTime());

        // Let's prevent pointless updates @todo account for omnidirectional sync, unless the config has been updated since last check then we do want to rebuild and check if the tagert object has changed
        if ($sourceHash === $synchronizationContract->getSourceHash() && $synchronization->getUpdated() < $synchronizationContract->getSourceLastChecked()) {
            // The object has not changed and the config has not been updated since last check
            // return $synchronizationContract;
            // @todo: somehow this always returns true, so we never do the updateTarget
        }

        // The object has changed, oke let do mappig and bla die bla
        $synchronizationContract->setSourceHash($sourceHash);
        $synchronizationContract->setSourceLastChanged(new DateTime());

        try {
            $mapping = $this->mappingMapper->find(id: $synchronization->getSourceTargetMapping());
        } catch (DoesNotExistException $exception) {
            return new Exception($exception->getMessage());
        }

        // let do the mapping if provided
        if ($synchronization->getSourceTargetMapping()){
            try {
                $sourceTargetMapping = $this->mappingMapper->find(id: $synchronization->getSourceTargetMapping());
            } catch (DoesNotExistException $exception) {
                throw new Exception("Could not find mapping with id: {$synchronization->getSourceTargetMapping()}");
            }
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
            $this->updateTarget(synchronizationContract: $synchronizationContract, targetObject: $targetObject);
        }

        // Log it
        $log = new SynchronizationContractLog();
        $log->setUuid(Uuid::v4());
        $log->setSynchronizationId($synchronizationContract->getSynchronizationId());
        $log->setSynchronizationContractId($synchronizationContract->getId());
        $log->setSource($object);
        $log->setTarget($targetObject);
        $log->setExpires(new DateTime('+1 day')); // @todo make this configurable

        if ($isTest === false) {
            $this->synchronizationContractLogMapper->insert($log);
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
	 * @return void
	 */
    public function updateTarget(SynchronizationContract $synchronizationContract, array $targetObject): void
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

        switch($type){
            case 'register/schema':
                // Setup the object service
                $objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');
                // if we alreadey have an id, we need to get the object and update it
                if ($synchronizationContract->getTargetId()){
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
        // Retrieve the source object based on the synchronization's source ID
        $source = $this->sourceMapper->find($synchronization->getSourceId());

        // Make the initial API call
        $response = $this->callService->call($source)->getResponse();
        $body = json_decode($response['body'], true);
        $objects = array_merge($objects, $this->getAllObjectsFromArray($body, $synchronization));

        // Return single object or empty array.
        if ($isTest === true) {
            return [$objects[0]] ?? [];
        }

        $nextLink = $this->getNextlinkFromCall($body, $synchronization);

        // Continue making API calls if there are more pages of results
        while ($nextLink !== null && $nextLink !== '' && $nextLink !== false) {
            $endpoint = str_replace($source->getLocation(), '', $nextLink);
            $response = $this->callService->call($source, $endpoint)->getResponse();
            $body = json_decode($response['body'], true);
            $objects = array_merge($objects, $this->getAllObjectsFromArray($body, $synchronization));
            $nextLink = $this->getNextlinkFromCall($body, $synchronization);
        }

        return $objects;
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
