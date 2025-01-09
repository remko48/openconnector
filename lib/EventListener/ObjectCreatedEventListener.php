<?php

namespace OCA\OpenConnector\EventListener;

use OCA\OpenConnector\Service\SynchronizationService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCA\OpenRegister\Event\ObjectCreatedEvent;
use OCA\OpenRegister\Event\ObjectUpdatedEvent;
use OCA\OpenRegister\Event\ObjectDeletedEvent;
use OCA\OpenConnector\Db\SynchronizationContractMapper;

class ObjectCreatedEventListener implements IEventListener
{

	public function __construct(
		private readonly SynchronizationService $synchronizationService
	)
	{
	}

	/**
     * @inheritDoc
     */
    public function handle(Event $event): void
    {
        if ($event instanceof ObjectCreatedEvent === false) {
			return;
		}
        $object = $event->getObject();
		$this->synchronizationService->synchronizeToTarget($object);
    }
}
