<?php

namespace OCA\OpenConnector\Service;


use OCA\OpenConnector\Cron\ActionTask;
use OCA\OpenConnector\Db\Job;
use OCA\OpenConnector\Db\JobMapper;
use OCP\BackgroundJob\IJobList;
use OCP\IDBConnection;

class JobService
{
    private IJobList $jobList;
    private JobMapper $jobMapper;
    private IDBConnection $connection;

    public function __construct( IJobList $jobList, JobMapper $jobMapper, ActionTask $actionTask, IDBConnection $connection) {

        $this->jobList = $jobList;
        $this->jobMapper = $jobMapper;
        $this->actionTask = $actionTask;
        $this->connection = $connection;
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
        $job->setJobListId($this->getJobListId($this->actionTask::class, $arguments));
        return $this->jobMapper->update($job);
    }

    	/**
	 * check if a job is in the list
	 *
	 * @param IJob|class-string<IJob> $job
	 * @param mixed $argument
	 */
	public function getJobListId($job, $argument): int|null {
		$class = ($job instanceof IJob) ? get_class($job) : $job;
		$arguments = json_encode($arguments);

		$query = $this->connection->getQueryBuilder();
		$query->select('id')
			->from('jobs')
			->where($query->expr()->eq('class', $query->createNamedParameter($class)))
			->andWhere($query->expr()->eq('argument_hash', $query->createNamedParameter(hash('sha256', $arguments))))
			->setMaxResults(1);

		$result = $query->executeQuery();
		$row = $result->fetch();
		$result->closeCursor();

		return $row['id'] ?? null;
	}

}
