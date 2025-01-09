<?php
return [
    'routes' => [
        // Existing routes...
        
        // Event messages
        ['name' => 'event#messages', 'url' => '/events/{id}/messages', 'verb' => 'GET'],
        
        // Subscription management
        ['name' => 'event#subscribe', 'url' => '/events/subscriptions', 'verb' => 'POST'],
        ['name' => 'event#updateSubscription', 'url' => '/events/subscriptions/{subscriptionId}', 'verb' => 'PUT'],
        ['name' => 'event#unsubscribe', 'url' => '/events/subscriptions/{subscriptionId}', 'verb' => 'DELETE'],
        ['name' => 'event#subscriptions', 'url' => '/events/subscriptions', 'verb' => 'GET'],
        ['name' => 'event#subscriptionMessages', 'url' => '/events/subscriptions/{subscriptionId}/messages', 'verb' => 'GET'],
        
        // Pull-based delivery
        ['name' => 'event#pull', 'url' => '/events/subscriptions/{subscriptionId}/pull', 'verb' => 'GET'],
    ]
]; 