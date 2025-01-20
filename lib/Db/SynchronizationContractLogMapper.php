<?php

namespace OCA\OpenConnector\Db;

use OCA\OpenConnector\Db\SynchronizationContractLog;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\ISession;
use OCP\IUserSession;
use Symfony\Component\Uid\Uuid;

/**
 * Class SynchronizationContractLogMapper
 * 
 * Mapper class for handling SynchronizationContractLog entities
 */
class SynchronizationContractLogMapper extends QBMapper
{
	public function __construct(
		IDBConnection $db,
		private readonly IUserSession $userSession,
		private readonly ISession $session
	) {
		parent::__construct($db, 'openconnector_synchronization_contract_logs');
	}

	public function find(int $id): SynchronizationContractLog
	{
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('openconnector_synchronization_contract_logs')
			->where(
				$qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
			);

		return $this->findEntity(query: $qb);
	}

	public function findOnSynchronizationId(string $synchronizationId): ?SynchronizationContractLog
	{
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('openconnector_synchronization_contract_logs')
			->where(
				$qb->expr()->eq('synchronization_id', $qb->createNamedParameter($synchronizationId))
			);

		try {
			return $this->findEntity($qb);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			return null;
		}
	}

	public function findAll(?int $limit = null, ?int $offset = null, ?array $filters = [], ?array $searchConditions = [], ?array $searchParams = []): array
	{
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from('openconnector_synchronization_contract_logs')
			->setMaxResults($limit)
			->setFirstResult($offset);

        foreach ($filters as $filter => $value) {
			if ($value === 'IS NOT NULL') {
				$qb->andWhere($qb->expr()->isNotNull($filter));
			} elseif ($value === 'IS NULL') {
				$qb->andWhere($qb->expr()->isNull($filter));
			} else {
				$qb->andWhere($qb->expr()->eq($filter, $qb->createNamedParameter($value)));
			}
        }

        if (empty($searchConditions) === false) {
            $qb->andWhere('(' . implode(' OR ', $searchConditions) . ')');
            foreach ($searchParams as $param => $value) {
                $qb->setParameter($param, $value);
            }
        }

		return $this->findEntities(query: $qb);
	}

	public function createFromArray(array $object): SynchronizationContractLog
	{
		$obj = new SynchronizationContractLog();
		$obj->hydrate($object);
		
		// Set uuid if not provided
		if ($obj->getUuid() === null){
			$obj->setUuid(Uuid::v4());
		}

		// Auto-fill userId from current user session
		if ($obj->getUserId() === null && $this->userSession->getUser() !== null) {
			$obj->setUserId($this->userSession->getUser()->getUID());
		}

		// Auto-fill sessionId from current session
		if ($obj->getSessionId() === null) {
			$obj->setSessionId($this->session->getId());
		}

		// If no synchronizationLogId is provided, we assume that the contract is run directly from the synchronization log and set the synchronizationLogId to n.a.
		if ($obj->getSynchronizationLogId() === null) {
			$obj->setSynchronizationLogId('n.a.');
		}

		return $this->insert($obj);
	}

	public function updateFromArray(int $id, array $object): SynchronizationContractLog
	{
		$obj = $this->find($id);
		$obj->hydrate($object);

		return $this->update($obj);
	}
}
