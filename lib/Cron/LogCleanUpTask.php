<?php

namespace OCA\OpenConnector\Cron;

use OCA\MyApp\DB\CallLogMapper;
use OCP\BackgroundJob\TimedJob;
use OCP\AppFramework\Utility\ITimeFactory;

class LogCleanUpTask
{
    public function doCron(array $arguments, CallLogMapper $callLogMapper){
        $callLogMapper = new ClearLogs();
    }

}
