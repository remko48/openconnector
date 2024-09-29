<?php

namespace OCA\OpenConnector\Cron;

use OCA\OpenConnector\Service\CallService;
use OCA\OpenConnector\Db\SourceMapper;
use OCA\OpenConnector\Db\JobMapper;
use OCA\OpenConnector\Db\JobLog;
use OCA\OpenConnector\Db\JobLogMapper;
use OCP\BackgroundJob\TimedJob;
use OCP\AppFramework\Utility\ITimeFactory;

/**
 * This class is used to run the action tasks for the OpenConnector app. It hooks into the cron job list and runs the classes that are set as the job class in the job.
 * 
 * @package OCA\OpenConnector\Cron
 */
class ActionTask extends TimedJob
{    
    private CallService $callService;
    private SourceMapper $sourceMapper;
    private JobMapper $jobMapper;
    private JobLogMapper $jobLogMapper;
    
    public function __construct(        
        ITimeFactory $time, 
        CallService $callService, 
        SourceMapper $sourceMapper, 
        JobMapper $jobMapper,
        JobLogMapper $jobLogMapper
    ) {
        parent::__construct($time);
        $this->callService = $callService;
        $this->sourceMapper = $sourceMapper;
        $this->jobMapper = $jobMapper;
        $this->jobLogMapper = $jobLogMapper;
        // Run every 5 minutes
        //$this->setInterval(300);

        // Delay until low-load time
        //$this->setTimeSensitivity(\OCP\BackgroundJob\IJob::TIME_SENSITIVE);
        // Or $this->setTimeSensitivity(\OCP\BackgroundJob\IJob::TIME_INSENSITIVE);
        
        // Only run one instance of this job at a time
        //$this->setAllowParallelRuns(false);
    }

    //@todo: make this a bit more generic :')
    public function run($argument)
    {
        // lets get the job
        $job = $this->jobMapper->find($argument['jobId']);
        // If the job is not enabled, we don't need to do anything
        if (!$job->isEnabled()) {
            return;
        }

        // if the next run is in the the future, we don't need to do anything
        if ($job->getNextRun() && $job->getNextRun() > $this->time->getTime()) {
            return;
        }

		$time_start = microtime(true); 
        
        // For now we only have one action, so this is a bit overkill, but it's a good starting point
        if (isset($arguments['sourceId']) && is_int($argument['sourceId'])) {
            $source = $this->sourceMapper->find($argument['sourceId']);
            $this->callService->call($source);
        }
        else {
            $source = $this->sourceMapper->find(1);
            $this->callService->call($source);
        }

        // @todo: instead get the actual call an run that

        $time_end = microtime(true);
        $executionTime = $time_end - $time_start;

        // deal with single run
        if ($job->isSingleRun()) {
            $job->setIsEnabled(false);
        }


        // Update the job
        $job->setLastRun($this->time->getTime());
        $job->setNextRun($this->time->getTime() + $job->getInterval());
        $this->jobMapper->update($job);

        // Log the job
        $jobLog = new JobLog();
        $jobLog->setJobId($job->getId());
        $jobLog->setJobClass($job->getJobClass());
        $jobLog->setJobListId($job->getJobListId());
        $jobLog->setArguments($job->getArguments());
        $jobLog->setLastRun($job->getLastRun());
        $jobLog->setNextRun($job->getNextRun());
        $jobLog->setExecutionTime($executionTime);
        $this->jobLogMapper->insert($jobLog);

        // Lets report back about what we have just done
        return;
    }

}
