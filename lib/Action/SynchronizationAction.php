<?php

namespace OCA\OpenConnector\Action;

use OCA\OpenConnector\Service\SynchronizationService;
use OCA\OpenConnector\Db\SynchronizationMapper;
use OCA\OpenConnector\Db\SynchronizationContractMapper;

/**
 * This action handles the synchronization of data from the source to the target.
 *
 * @package OCA\OpenConnector\Cron
 */
class SynchronizationAction
{
    private SynchronizationService $synchronizationService;
    private SynchronizationMapper $synchronizationMapper;
    private SynchronizationContractMapper $synchronizationContractMapper;
    public function __construct(
        SynchronizationService $synchronizationService,
        SynchronizationMapper $synchronizationMapper,
        SynchronizationContractMapper $synchronizationContractMapper,
    ) {
        $this->synchronizationService = $synchronizationService;
        $this->synchronizationMapper = $synchronizationMapper;
        $this->synchronizationContractMapper = $synchronizationContractMapper;
    }

    //@todo: make this a bit more generic :')
    public function run($argument)
    {
        //@todo: for testing purposes
        if (!isset($argument['synchronizationId'])) {
            $argument['synchronizationId'] = 1;
        }

        $response = [];

        // if we do not have a synchronization Id then everything is wrong
        $response['stackTrace'][] = 'Check for a valid synchronization ID';
        if (!isset($argument['synchronizationId'])) {
            // @todo: implement error handling
            $response['level'] = 'WARNING';
            $response['message'] = 'No synchronization ID provided';
            return $response;
        }

        // We are going to allow for a single synchronization contract to be processed at a time
        if (isset($argument['synchronizationContractId']) && is_int((int) $argument['synchronizationContractId'])) {
            $synchronizationContract = $this->synchronizationContractMapper->find((int) $argument['synchronizationContractId']);
            $this->callService->synchronizeContract($synchronization);
            return $response;
        }

        // Lets find a synchronysation
        $response['stackTrace'][] = 'Getting synchronization';
        $synchronization = $this->synchronizationMapper->find((int) $argument['synchronizationId']);
        if (!$synchronization){
            $response['level'] = 'WARNING';
            $response['message'] = 'No synchronization found';
            return $response;
        }

        // Doing the synchronization
        $response['stackTrace'][] = 'Doing the synchronization';
        $objects = $this->synchronizationService->synchronize($synchronization);

        $response['stackTrace'][] = 'Synchronized '.count($objects).' successfully';

        // Lets report back about what we have just done
        return $response;
    }

}
