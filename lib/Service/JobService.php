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

    public function __construct( IJobList $jobList, JobMapper $jobMapper, ActionTask $actionTask) {

        $this->jobList = $jobList;
        $this->jobMapper = $jobMapper;
        $this->actionTask = $actionTask;
    }

    public function scheduleJob(Job $job): Job
    {
        // Lets first check if the job should be disabled
        if (!$job->getIsEnabled() || $job->getJobListId()) {

            $this->jobList->removeById($job->getId());
            $job->setJobListId(null);
            return $this->jobMapper->save(job);
        }

        // lets not update the job if it's already scheduled @todo we should
        if($job->getJobListId()) {
            return $job;
        }

        // Oke this is a new job lets schedule it
        $arguments = $job->getArguments();
        $arguments['jobId'] = $job->getId();

        if(!$job->getScheduleAfter()) {
            $iJob = $this->jobList->add($this->actionTask::class, $arguments);
        } else {
            $runAfter = $job->getScheduleAfter()->getTimestamp();
            $iJob = $this->jobList->scheduleAfter($this->actionTask::class, $runAfter, $arguments);
        }

        // Save the job to the database
        // @todo how  do we get the job id?
        ///$job->setJobListId($iJob->getId());
        return $this->jobMapper->update($job);
        // $this->jobList->add($job->getJobClass(), $job->getArguments());
    }

}
