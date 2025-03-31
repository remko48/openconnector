<?php

namespace OCA\OpenConnector\EventListener;

use OCA\OpenConnector\Service\SynchronizationService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCA\OpenRegister\Event\ObjectCreatedEvent;
use OCA\OpenRegister\Event\ObjectUpdatedEvent;
use OCA\OpenRegister\Event\ObjectDeletedEvent;
use OCA\OpenConnector\Db\SynchronizationContractMapper;

class ObjectDeletedEventListener implements IEventListener
{


    public function __construct(
        private readonly SynchronizationService $synchronizationService,
        private readonly SynchronizationContractMapper $synchronizationContractMapper,
    ) {

    }//end __construct()


    /**
     * @inheritDoc
     */
    public function handle(Event $event): void
    {
        if ($event instanceof ObjectDeletedEvent === false) {
            return;
        }

        $object = $event->getObject();

        $contracts = $this->synchronizationContractMapper->handleObjectRemoval($object->getUuid());

        foreach ($contracts as $contract) {
            $this->synchronizationService->synchronizeToTarget($object, $contract);
        }

    }//end handle()


}//end class
