<?php

declare(strict_types=1);

namespace OCA\OpenConnector\AppInfo;

use OCA\OpenConnector\EventListener\ObjectCreatedEventListener;
use OCA\OpenConnector\EventListener\ObjectDeletedEventListener;
use OCA\OpenConnector\EventListener\ObjectUpdatedEventListener;
use OCA\OpenConnector\Service\SynchronizationObjectProcessor;
use OCA\OpenConnector\Service\TargetHandler\ApiHandler;
use OCA\OpenConnector\Service\TargetHandler\OpenRegisterHandler;
use OCA\OpenConnector\Service\TargetHandler\TargetHandlerRegistry;
use OCA\OpenRegister\Event\ObjectCreatedEvent;
use OCA\OpenRegister\Event\ObjectDeletedEvent;
use OCA\OpenRegister\Event\ObjectUpdatedEvent;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\EventDispatcher\IEventDispatcher;

class Application extends App implements IBootstrap
{
    public const APP_ID = 'openconnector';


    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct()
    {
        parent::__construct(self::APP_ID);

    }//end __construct()


    public function register(IRegistrationContext $context): void
    {
        include_once __DIR__.'/../../vendor/autoload.php';

        // Register target handlers
        $context->registerService(
            TargetHandlerRegistry::class,
            function ($c) {
                $registry = new TargetHandlerRegistry($c);
                $registry->registerHandler($c->get(OpenRegisterHandler::class));
                $registry->registerHandler($c->get(ApiHandler::class));
                return $registry;
            }
        );

        // Register event listeners
        $dispatcher = $this->getContainer()->get(IEventDispatcher::class);
        $dispatcher->addServiceListener(eventName: ObjectCreatedEvent::class, className: ObjectCreatedEventListener::class);
        $dispatcher->addServiceListener(eventName: ObjectUpdatedEvent::class, className: ObjectUpdatedEventListener::class);
        $dispatcher->addServiceListener(eventName: ObjectDeletedEvent::class, className: ObjectDeletedEventListener::class);

    }//end register()


    public function boot(IBootContext $context): void
    {

    }//end boot()


}//end class
