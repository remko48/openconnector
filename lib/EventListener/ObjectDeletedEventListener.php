<?php

namespace OCA\OpenConnector\EventListener;

use OCA\OpenConnector\Service\SynchronizationService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCA\OpenRegister\Event\ObjectDeletedEvent;
use Psr\Log\LoggerInterface;

/**
 * Listener that forwards object changes to the SynchronizationService
 */
class ObjectDeletedEventListener implements IEventListener
{
    /**
     * @param SynchronizationService $synchronizationService Service for synchronizing
     * @param LoggerInterface $logger Logger instance
     */
    public function __construct(
        private readonly SynchronizationService $synchronizationService,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Handle incoming events by forwarding them to the SynchronizationService
     *
     * @param Event $event The incoming event
     * @return void
     */
    public function handle(Event $event): void
    {
        if ($event instanceof ObjectDeletedEvent === false
        ) {
            return;
        }

        if (method_exists($event, 'getObject') === false) {
            return;
        }


        $object = $event->getObject();
        if ($object === null || $object->getRegister() === null || $object->getSchema() === null) {
            return;
        }

        $synchronizations = $this->synchronizationService->findAllBySourceId(register: $object->getRegister(), schema: $object->getSchema());
        foreach ($synchronizations as $synchronization) {
            try {
                $log = $this->synchronizationService->createSynchronizationLog(synchronization: $synchronization, isTest: false, force: true, type: 'delete-extern');
                $this->synchronizationService->processSynchronizationObject(synchronization: $synchronization, object: $object, result: [], isTest: false, force: true, log: $log);
            } catch (\Exception $e) {
                $this->logger->error('Failed to process object event: ' . $e->getMessage() . ' for synchronization ' . $synchronization->getId(), [
                    'exception' => $e,
                    'event' => get_class($event)
                ]);
            }
        }
    }
}