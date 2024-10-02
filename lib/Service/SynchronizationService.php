<?php

namespace OCA\OpenConnector\Service;

use OCA\OpenConnector\Db\CallLog; 
use OCA\OpenConnector\Db\Source;
use OCA\OpenConnector\Db\SourceMapper;
use OCA\OpenConnector\Db\Synchronization;
use OCA\OpenConnector\Db\SynchronizationMapper;
use OCA\OpenConnector\Db\SynchronizationContract;
use OCA\OpenConnector\Db\SynchronizationContractMapper;
use OCA\OpenConnector\Service\CallService;
use OCA\OpenConnector\Service\MappingService;

use Psr\Container\ContainerInterface;
use DateInterval;
use DateTime;


class SynchronizationService
{
    private CallService $callService;
    private MappingService $mappingService;
    private ContainerInterface $containerInterface; 
    private Synchronization $synchronization;
    private SynchronizationMapper $synchronizationMapper;
    private SourceMapper $sourceMapper;
    private SynchronizationContractMapper $synchronizationContractMapper;
    private ObjectService $objectService;
    private Source $source;


	public function __construct(
		CallService $callService,
		MappingService $mappingService,
		ContainerInterface $containerInterface,
        SourceMapper $sourceMapper,
		SynchronizationMapper $synchronizationMapper,
		SynchronizationContractMapper $synchronizationContractMapper
	) {
		$this->callService = $callService;
		$this->mappingService = $mappingService;
		$this->containerInterface = $containerInterface;
		$this->synchronizationMapper = $synchronizationMapper;
		$this->synchronizationContractMapper = $synchronizationContractMapper;
        $this->sourceMapper = $sourceMapper;
	}

    /**
     * Synchronizes a given synchronization (or a complete source).
     * 
     * @param Synchronization $synchronization
     * @return void
     */
    public function synchronize(Synchronization $synchronization)
    {
        $objectList = $this->getAllObjectsFromSource($synchronization);

        foreach($objectList as $key => $object) {
            // Get the synchronization contract for this object
            $synchronizationContract = $this->synchronizationContractMapper->findOnSynchronizationIdSourceId($synchronization->id, $object['id']);
            
            if (!($synchronizationContract instanceof SynchronizationContract)) {
                $synchronizationContract = new SynchronizationContract();
                $synchronizationContract->setSynchronizationId($synchronization->id);
                $synchronizationContract->setSourceId($object['id']);
                $synchronizationContract->setSourceHash(md5(serialize($object)));
                
                $synchronizationContract = $this->synchronizeContract($synchronizationContract, $synchronization, $object);
                $objectList[$key] = $this->synchronizationContractMapper->insert($synchronizationContract);

            }
            else{
                // @todo this is wierd                
                $synchronizationContract = $this->synchronizeContract($synchronizationContract, $synchronization, $object);
                $objectList[$key] = $this->synchronizationContractMapper->update($synchronizationContract);
            }         
        }

        return $objectList;
    }

    /**
     * Synchronize a contract
     * @param SynchronizationContract $synchronizationContract  
     * @param Synchronization $synchronization
     * @param array $object
     * 
     * @return SynchronizationContract
     */
    public function synchronizeContract(SynchronizationContract $synchronizationContract, Synchronization $synchronization = null, array $object = [])
    {
        // Let create a source hash for the object
        $sourceHash = md5(serialize($object));
        $synchronizationContract->setSourceLastChecked(new DateTime()); 

        // Lets prevent pointless updates @todo acount for omnidirectional sync
        if($sourceHash === $synchronizationContract->getSourceHash()){
            // The object has not changed
            return $synchronizationContract; // Fix: Add $ before synchronizationContract
        }

        // The object has changed, oke let do mappig and bla die bla
        $synchronizationContract->setSourceHash($sourceHash);
        $synchronizationContract->setSourceLastChanged(new DateTime());

        // let do the mapping if provided
        if($synchronization->getSourceTargetMapping()){
            $targetObject = $this->mappingService->mapping($synchronization->getSourceTargetMapping(), $object);
        }
        else{
            $targetObject = $object;
        }


        // set the target hash
        $targetHash = md5(serialize($targetObject));
        $synchronizationContract->setTargetHash($targetHash);
        $synchronizationContract->setTargetLastChanged(new DateTime());
        $synchronizationContract->setTargetLastSynced(new DateTime());
        $synchronizationContract->setSourceLastSynced(new DateTime());

        // Do the magic!!

        $this->updateTarget($synchronizationContract, $targetObject);

        return $synchronizationContract;

    }

    /**
     *  Write the data to the target
     * 
     * @param SynchronizationContract $synchronizationContract
     * @return void
     */
    public function updateTarget(SynchronizationContract $synchronizationContract, array $targetObject)
    {
         // The function can be called solo set let's make sure we have the full synchronization object
         if(!$synchronization){
            $synchronization = $this->synchronizationMapper->find($synchronizationContract->getSynchronizationId());
        }

        // Lets check if we need to create or update
        $update = false;
        if($synchronizationContract->getTargetId()){
            $update = true;
        }

        $type = $synchronization->getTargetType(); 

        switch($type){
            case 'register/schema':
                // Setup the object service
                $objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');
                // if we alreadey have an id, we need to get the object and update it
                if($synchronizationContract->getTargetId()){
                    $targetObject['id'] = $synchronizationContract->getTargetId();
                }
                // Extract register and schema from the targetId
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
                throw new \Exception("Unsupported target type: $type");
        }
    }

    /**
     * Get all the object from a source
     * 
     * @param SynchronizationContract $synchronizationContract
     * @return void
     */
    public function getAllObjectsFromSource(Synchronization $synchronization)
    {
        $objects = [];

        $type = $synchronization->getSourceType(); 

        switch($type){
            case 'register/schema':
                // Setup the object service
                $this->objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');
                
                break;
            case 'api':                
                //@todo: implement
                $source = $this->sourceMapper->find($synchronization->getSourceId());
                $sourceObject = $this->callService->call($source);
                $objects = $this->getAllObjectsFromArray($sourceObject, $source);
                break;
            case 'database':
                //@todo: implement
                break;
        }
        return $objects;
    }

    public function getAllObjectsFromArray(CallLog $callLog, Source $source)
    {

        // lekeer hacky (only works on github for now)
        //$sourceObject = $this->callService->get($source->getUrl());
        $response = $callLog->getResponse();
        $body = json_decode($response['body'], true);
        return $body['items'];
    }
}