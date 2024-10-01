<?php

namespace OCA\OpenConnector\Service;

use OCA\OpenConnector\Db\Source;
use OCA\OpenConnector\Db\Synchronization;
use OCA\OpenConnector\Db\SynchronizationMapper;
use OCA\OpenConnector\Db\SynchronizationContract;
use OCA\OpenConnector\Db\SynchronizationContractMapper;
use OCA\OpenConnector\Service\CallService;
use OCA\OpenConnector\Service\MappingService;

use Psr\Container\ContainerInterface;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Adbar\Dot;
use DateInterval;
use DateTime;


class SynchronizationService
{
    private CallService $callService;
    private MappingService $mappingService;
    private ContainerInterface $containerInterface; 
    private Synchronization $synchronization;
    private SynchronizationMapper $synchronizationMapper;
    private SynchronizationContractMapper $synchronizationContractMapper;
    private ObjectService $objectService;


	public function __construct(
		CallService $callService,
		MappingService $mappingService,
		ContainerInterface $containerInterface,
		SynchronizationMapper $synchronizationMapper,
		SynchronizationContractMapper $synchronizationContractMapper
	) {
		$this->callService = $callService;
		$this->mappingService = $mappingService;
		$this->containerInterface = $containerInterface;
		$this->synchronizationMapper = $synchronizationMapper;
		$this->synchronizationContractMapper = $synchronizationContractMapper;
	}

    /**
     * Synchronizes a given synchronization (or a complete source).
     * 
     * @param Synchronization $synchronization
     * @return void
     */
    public function synchronize(Synchronization $synchronization)
    {
        $this->synchronization = $synchronization;
        $objectList = [];

        foreach($objectList as $object) {
            // Get the synchronization contract for this object
            $synchronizationContract = $this->synchronizationContractMapper->findOnSource($synchronization->id, $object['id']);
            if(!$synchronizationContract) {
                $synchronizationContract = new SynchronizationContract();
                $synchronizationContract->setSynchronizationId($synchronization->id);
                $synchronizationContract->setSourceId($object['id']);
                $synchronizationContract->setSourceHash(md5(serialize($object)));
                // @todo: should we do this here
                $this->synchronizationContractMapper->insert($synchronizationContract);
            }

            $this->synchronizeContract($synchronizationContract);
        }

    }

    /**
     * @param SynchronizationContract $synchronizationContract
     * @return void
     */
    public function synchronizeContract(SynchronizationContract $synchronizationContract, $object = null)
    {
        // The function can be called solo set let's make sure we have the full synchronization object
        if(!$this->synchronization){
            $this->synchronization = $this->synchronizationMapper->findById($synchronizationContract->getSynchronizationId());
        }

        // We should have an object but lets make sure we have the full object
        if(!$object){
            $object = $this->getAllObjectsFromSource($synchronizationContract);
        }

        // Let create a source hash for the object
        $sourceHash = md5(serialize($object));
        $synchronizationContract->sourceLastChecked(new DateTime());        

        // Lets prevent pointless updates @todo acount for omnidirectional sync
        if($sourceHash === $synchronizationContract->getSourceHash()){
            // The object has not changed
            return $this->synchronizationContractMapper->update($synchronizationContract);
        }

        // The object has changed, oke let do mappig and bla die bla
        $synchronizationContract->setSourceHash($sourceHash);
        $synchronizationContract->sourceLastChanged(new DateTime());

        // let do the mapping if provided
        if($synchronizationContract->getSourceTargetMapping()){
            $targetObject = $this->mappingService->mapping($synchronizationContract->getSourceTargetMapping(), $object);
        }
        else{
            $targetObject = $object;
        }

        // set the target hash
        $targetHash = md5(serialize($targetObject));
        $synchronizationContract->setTargetHash($targetHash);
        $synchronizationContract->targetLastChanged(new DateTime());
        $synchronizationContract->targetLastSynced(new DateTime());
        $synchronizationContract->sourceLastSynced(new DateTime());

        // Do the magic!!

        $this->updateTarget($synchronizationContract, $targetObject);

        // Save results
        $this->synchronizationContractMapper->update($synchronizationContract);

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
        if(!$this->synchronization){
            $this->synchronization = $this->synchronizationMapper->findById($synchronizationContract->getSynchronizationId());
        }

        // Lets check if we need to create or update
        $update = false;
        if($synchronizationContract->getTargetId()){
            $update = true;
        }

        $type = $synchronizationContract->getTargetType(); 

        switch($type){
            case 'register/schema':
                // Setup the object service
                $this->objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');
                // if we alreadey have an id, we need to get the object and update it
                if($synchronizationContract->getTargetId()){
                    $targetObject['id'] = $synchronizationContract->getTargetId();
                }
                // Extract register and schema from the targetId
                $targetId = $this->synchronization->getTargetId();
                list($register, $schema) = explode('/', $targetId);
                
                // Save the object to the target
                $target = $this->objectService->saveObject($register, $schema, $targetObject);
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
        switch($type){
            case 'register/schema':
                // Setup the object service
                $this->objectService = $this->containerInterface->get('OCA\OpenRegister\Service\ObjectService');
                
                break;
            case 'api':
                
                //@todo: implement
                //$this->callService->put($targetObject);
                break;
            case 'database':
                //@todo: implement
                break;
        }
    }
}