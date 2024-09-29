<?php

namespace OCA\OpenConnector\Service;


use OCA\OpenConnector\Cron\ActionTask;
use OCA\OpenConnector\Db\Job;
use OCA\OpenConnector\Db\JobMapper;
use OCP\BackgroundJob\IJobList;

class JobService
{
    private IJobList $jobList;
    private JobMapper $jobMapper;

    public function __construct( IJobList $jobList, JobMapper $jobMapper) {

        $this->jobList = $jobList;
        $this->jobMapper = $jobMapper;
    }

    public function scheduleJob(Job $job): Job
    {
        // Lets first check if the job should be disabled
        if (!$job->isEnabled() || $job->getJobListId()) {

            $this->jobList->removeById($job->getId());
            $job->setJobListId(null);
            return $this->jobMapper->save(job);
        }

        // lets not update the job if it's already scheduled @todo we should
        if($job->getJobListId()) {
            return $job;
        }

        // Oke this is a new job lets schedule it
        $actionTask = new ActionTask();
        $arguments = $job->getArguments();
        $arguments['jobId'] = $job->getId();

        if(!$job->getScheduleAfter()) {
            $iJob = $this->jobList->add($actionTask::class, $arguments);
        } else {
            $runAfter = $job->getScheduleAfter()->getTimestamp();
            $iJob = $this->jobList->scheduleAfter($actionTask::class, $runAfter, $arguments);
        }

        // Save the job to the database
        $job->setJobListId($iJob->getId());
        return $this->jobMapper->save($job);
        // $this->jobList->add($job->getJobClass(), $job->getArguments());
    }

}
