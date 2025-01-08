<?php

namespace OCA\OpenConnector\EventListener;

use OCA\OpenConnector\Service\SynchronizationService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCA\OpenRegister\Event\ObjectCreatedEvent;
use OCA\OpenRegister\Event\ObjectUpdatedEvent;
use OCA\OpenRegister\Event\ObjectDeletedEvent;

class ObjectEventListener implements IEventListener
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
        if ($event instanceof ObjectCreatedEvent === false
			&& $event instanceof ObjectUpdatedEvent === false
			&& $event instanceof ObjectDeletedEvent === false
		) {
			return;
		}

		if($event instanceof ObjectCreatedEvent === true) {
			$object = $event->getObject();
			$this->synchronizationService->synchronizeToTarget($object);
		} else if ($event instanceof ObjectUpdatedEvent === true) {
			$object = $event->getNewObject();
			$this->synchronizationService->synchronizeToTarget($object);
		} else {
			$this->synchronizationService->removeObjectFromTarget($object);
		}
    }
}
