<?php

namespace OCA\OpenConnector\Action;

use OCA\OpenConnector\Service\CallService;
use OCA\OpenConnector\Db\SynchronizationMapper;
use OCA\OpenConnector\Db\SynchronizationContractMapper;

/**
 * This action handles the synchronization of data from the source to the target.
 * 
 * @package OCA\OpenConnector\Cron
 */
class SynchronizationAction 
{    
    private CallService $callService;
    private SynchronizationMapper $synchronizationMapper;
    private SynchronizationContractMapper $synchronizationContractMapper;
    public function __construct(      
        CallService $callService, 
        SynchronizationMapper $synchronizationMapper, 
        SynchronizationContractMapper $synchronizationContractMapper,
    ) {
        $this->callService = $callService;
        $this->synchronizationMapper = $synchronizationMapper;
        $this->synchronizationContractMapper = $synchronizationContractMapper;
    }

    //@todo: make this a bit more generic :')
    public function run($argument)
    {

        
        // if we do not have a synchronization Id then everything is wrong
        if (isset($arguments['synchronizationId']) && is_int($argument['synchronizationId'])) {
            // @todo: implement error handling
            return;
        }

        // We are going to allow for a single synchronization contract to be processed at a time
        if (isset($arguments['synchronizationContractId']) && is_int($argument['synchronizationContractId'])) {
            $synchronizationContract = $this->synchronizationContractMapper->find($argument['synchronizationContractId']);

            return;
        }

        // oke lets synchronyse a source, why not
        $synchronization = $this->synchronizationMapper->find($argument['synchronizationId']);

        // @todo: implement this

        // Lets report back about what we have just done
        return;
    }

}
