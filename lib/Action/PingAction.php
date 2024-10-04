<?php

namespace OCA\OpenConnector\Action;

use OCA\OpenConnector\Service\CallService;
use OCA\OpenConnector\Db\SourceMapper;

/**
 * This class is used to run the action tasks for the OpenConnector app. It hooks into the cron job list and runs the classes that are set as the job class in the job.
 *
 * @package OCA\OpenConnector\Cron
 */
class PingAction
{
    private CallService $callService;
    private SourceMapper $sourceMapper;

    public function __construct(
        CallService $callService,
        SourceMapper $sourceMapper,
    ) {
        $this->callService = $callService;
    }

    //@todo: make this a bit more generic :')
    public function run($arguments)
    {
		echo json_encode($arguments);
        // For now we only have one action, so this is a bit overkill, but it's a good starting point
        if (isset($arguments['sourceId']) && is_int((int) $arguments['sourceId'])) {
            $source = $this->sourceMapper->find((int) $arguments['sourceId']);
		}
        else {
			// @todo log and / or not default to just using the first source
            $source = $this->sourceMapper->find(1);
		}

		$this->callService->call($source);

		// Lets report back about what we have just done
        return;
    }

}
