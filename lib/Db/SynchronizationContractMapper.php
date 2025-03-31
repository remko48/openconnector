<?php
/**
 * OpenConnector - Connect your Nextcloud to external services
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @category  Mapper
 * @package   OpenConnector
 * @author    Conduction Development Team <dev@conductio.nl>
 * @copyright 2024 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * @version   GIT: <git-id>
 * @link      https://OpenConnector.app
 */

namespace OCA\OpenConnector\Db;

use OCA\OpenConnector\Db\SynchronizationContract;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use Symfony\Component\Uid\Uuid;

/**
 * Mapper class for SynchronizationContract entities
 *
 * This class handles database operations for synchronization contracts including
 * CRUD operations and specialized queries.
 *
 * @psalm-suppress  PropertyNotSetInConstructor
 * @phpstan-extends QBMapper<SynchronizationContract>
 */
class SynchronizationContractMapper extends QBMapper
{


    /**
     * Constructor for SynchronizationContractMapper
     *
     * @param         IDBConnection $db Database connection instance
     * @psalm-param   IDBConnection $db
     * @phpstan-param IDBConnection $db
     * @return        void
     */
    public function __construct(IDBConnection $db)
    {
        parent::__construct($db, 'openconnector_synchronization_contracts');

    }//end __construct()


    /**
     * Find a synchronization contract by ID
     *
     * @param         int $id The ID of the contract to find
     * @psalm-param   int $id
     * @phpstan-param int $id
     * @return        SynchronizationContract The found contract entity
     * @throws        \OCP\AppFramework\Db\DoesNotExistException If contract not found
     * @throws        \OCP\AppFramework\Db\MultipleObjectsReturnedException If multiple contracts match
     */
    public function find(int $id): SynchronizationContract
    {
        // Create query builder.
        $qb = $this->db->getQueryBuilder();

        // Build select query with ID filter.
        $qb->select('*')
            ->from('openconnector_synchronization_contracts')
            ->where(
                $qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
            );

        return $this->findEntity(query: $qb);

    }//end find()


    /**
     * Find a synchronization contract by synchronization ID and origin ID
     *
     * @param string $synchronizationId The synchronization ID
     * @param string $originId          The origin ID
     *
     * @return SynchronizationContract|null The found contract or null if not found
     * @throws MultipleObjectsReturnedException
     * @throws Exception
     */
    public function findSyncContractByOriginId(string $synchronizationId, string $originId): ?SynchronizationContract
    {
        // Create query builder
        $qb = $this->db->getQueryBuilder();

        // Build select query with synchronization and origin ID filters
        $qb->select('*')
            ->from('openconnector_synchronization_contracts')
            ->where(
                $qb->expr()->eq('synchronization_id', $qb->createNamedParameter($synchronizationId))
            )
            ->andWhere(
                $qb->expr()->eq('origin_id', $qb->createNamedParameter($originId))
            );

        try {
            return $this->findEntity($qb);
        } catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
            return null;
        }

    }//end findSyncContractByOriginId()


    /**
     * Find a synchronization contract by synchronization ID and target ID
     *
     * @param  string $synchronization The synchronization ID
     * @param  string $targetId        The target ID
     * @return SynchronizationContract|bool|null The found contract, false, or null if not found
     */
    public function findOnTarget(string $synchronization, string $targetId): SynchronizationContract | bool | null
    {
        // Create query builder
        $qb = $this->db->getQueryBuilder();

        // Build select query with synchronization and target ID filters
        $qb->select('*')
            ->from('openconnector_synchronization_contracts')
            ->where(
                $qb->expr()->eq('synchronization_id', $qb->createNamedParameter($synchronization))
            )
            ->andWhere(
                $qb->expr()->eq('target_id', $qb->createNamedParameter($targetId))
            );

        try {
            return $this->findEntity($qb);
        } catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
            return null;
        }

    }//end findOnTarget()


    /**
     * Find a synchronization contract by origin ID and target ID
     *
     * @param  string $originId The origin ID
     * @param  string $targetId The target ID
     * @return SynchronizationContract|bool|null The found contract, false, or null if not found
     */
    public function findByOriginAndTarget(string $originId, string $targetId): SynchronizationContract | bool | null
    {
        // Create query builder
        $qb = $this->db->getQueryBuilder();

        // Build select query with synchronization and target ID filters
        $qb->select('*')
            ->from('openconnector_synchronization_contracts')
            ->where(
                $qb->expr()->eq('origin_id', $qb->createNamedParameter($originId))
            )
            ->andWhere(
                $qb->expr()->eq('target_id', $qb->createNamedParameter($targetId))
            );

        try {
            return $this->findEntity($qb);
        } catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
            return null;
        }

    }//end findByOriginAndTarget()


    /**
     * Find all synchronization contracts by synchronization ID and where target have the given schema id
     *
     * @param string $synchronization The synchronization ID
     *
     * @return array An array of target IDs or an empty array if none found
     */
    public function findAllBySynchronizationAndSchema(string $synchronizationId, string $schemaId): array
    {
        // Create query builder
        $qb = $this->db->getQueryBuilder();

        // Build select query with synchronization ID and schema filter
        $qb->select('c.*')
            ->from('openconnector_synchronization_contracts', 'c')
            ->innerJoin(
                'c',
                'oc_openregister_objects',
                'o',
                $qb->expr()->eq('c.target_id', 'o.uuid')
            )
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('c.synchronization_id', $qb->createNamedParameter($synchronizationId)),
                    $qb->expr()->eq('o.schema', $qb->createNamedParameter($schemaId))
                )
            );

        try {
            return $this->findEntities($qb);
        } catch (\Exception $e) {
            return [];
        }

    }//end findAllBySynchronizationAndSchema()


    /**
     * Find all synchronization contracts with optional filtering and pagination
     *
     * @param          int|null   $limit            Maximum number of results to return
     * @param          int|null   $offset           Number of results to skip
     * @param          array|null $filters          Associative array of field => value filters
     * @param          array|null $searchConditions Array of search conditions
     * @param          array|null $searchParams     Array of search parameters
     * @psalm-param    int|null $limit
     * @psalm-param    int|null $offset
     * @psalm-param    array<string, mixed>|null $filters
     * @psalm-param    array<int, string>|null $searchConditions
     * @psalm-param    array<string, mixed>|null $searchParams
     * @phpstan-param  int|null $limit
     * @phpstan-param  int|null $offset
     * @phpstan-param  array<string, mixed>|null $filters
     * @phpstan-param  array<int, string>|null $searchConditions
     * @phpstan-param  array<string, mixed>|null $searchParams
     * @return         SynchronizationContract[] Array of found contracts
     * @psalm-return   array<int, SynchronizationContract>
     * @phpstan-return array<int, SynchronizationContract>
     */
    public function findAll(
        ?int $limit=null,
        ?int $offset=null,
        ?array $filters=[],
        ?array $searchConditions=[],
        ?array $searchParams=[]
    ): array {
        // Create query builder
        $qb = $this->db->getQueryBuilder();

        // Build base select query with pagination
        $qb->select('*')
            ->from('openconnector_synchronization_contracts')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        // Add filters if provided
        foreach ($filters as $filter => $value) {
            if ($value === 'IS NOT NULL') {
                $qb->andWhere($qb->expr()->isNotNull($filter));
            } else if ($value === 'IS NULL') {
                $qb->andWhere($qb->expr()->isNull($filter));
            } else {
                $qb->andWhere($qb->expr()->eq($filter, $qb->createNamedParameter($value)));
            }
        }

        // Add search conditions if provided
        if (empty($searchConditions) === false) {
            $qb->andWhere('('.implode(' OR ', $searchConditions).')');
            foreach ($searchParams as $param => $value) {
                $qb->setParameter($param, $value);
            }
        }

        return $this->findEntities(query: $qb);

    }//end findAll()


    /**
     * Create a new synchronization contract from array data
     *
     * @param  array $object Array of contract data
     * @return SynchronizationContract The created contract entity
     */
    public function createFromArray(array $object): SynchronizationContract
    {
        // Create and hydrate new contract object
        $obj = new SynchronizationContract();
        $obj->hydrate(object: $object);

        // Set uuid
        if ($obj->getUuid() === null) {
            $obj->setUuid(Uuid::v4());
        }

        // Set version
        if (empty($obj->getVersion()) === true) {
            $obj->setVersion('0.0.1');
        }

        return $this->insert(entity: $obj);

    }//end createFromArray()


    /**
     * Update an existing synchronization contract from array data
     *
     * @param  int   $id     ID of contract to update
     * @param  array $object Array of updated contract data
     * @return SynchronizationContract The updated contract entity
     */
    public function updateFromArray(int $id, array $object): SynchronizationContract
    {
        // Find and hydrate existing contract
        $obj = $this->find($id);

        // Set version
        if (empty($obj->getVersion()) === true) {
            $object['version'] = '0.0.1';
        } else if (empty($object['version']) === true) {
            // Update version
            $version = explode('.', $obj->getVersion());
            if (isset($version[2]) === true) {
                $version[2]        = ((int) $version[2] + 1);
                $object['version'] = implode('.', $version);
            }
        }

        $obj->hydrate($object);

        return $this->update($obj);

    }//end updateFromArray()


    /**
     * Find a synchronization contract by origin ID.
     *
     * @param string $originId The origin ID to search for.
     *
     * @return SynchronizationContract|null The matching contract or null if not found.
     */
    public function findByOriginId(string $originId): ?SynchronizationContract
    {
        // Create query builder
        $qb = $this->db->getQueryBuilder();

        // Build query to find contract matching origin_id
        $qb->select('*')
            ->from('openconnector_synchronization_contracts')
            ->where(
                $qb->expr()->eq('origin_id', $qb->createNamedParameter($originId))
            )
            ->setMaxResults(1);
        // Ensure only one result is returned
        try {
            return $this->findEntity($qb);
            // Use findEntity to return a single result
        } catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
            return null;
            // Return null if no match is found
        }

    }//end findByOriginId()


    /**
     * Find a synchronization contract by target ID.
     *
     * @param string $targetId The target ID to search for.
     *
     * @return SynchronizationContract[] The matching contract or null if not found.
     */
    public function findByTargetId(string $targetId): array
    {
        // Create query builder
        $qb = $this->db->getQueryBuilder();

        // Build query to find contract matching origin_id
        $qb->select('*')
            ->from('openconnector_synchronization_contracts')
            ->where(
                $qb->expr()->eq('target_id', $qb->createNamedParameter($targetId))
            );
        // Ensure only one result is returned
        try {
            return $this->findEntities($qb);
            // Use findEntity to return a single result
        } catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
            return [];
            // Return null if no match is found
        }

    }//end findByTargetId()


    /**
     * Find synchronization contracts by type and ID
     *
     * @param  string $type The type to search for (e.g., 'user', 'group')
     * @param  string $id   The ID to search for
     * @return array<SynchronizationContract> Array of matching contracts
     */
    public function findByTypeAndId(string $type, string $id): array
    {
        // Create query builder
        $qb = $this->db->getQueryBuilder();

        // Build query to find contracts matching type/id as either source or target
        $qb->select('*')
            ->from('openconnector_synchronization_contracts')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->eq('source_type', $qb->createNamedParameter($type)),
                        $qb->expr()->eq('origin_id', $qb->createNamedParameter($id))
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->eq('target_type', $qb->createNamedParameter($type)),
                        $qb->expr()->eq('target_id', $qb->createNamedParameter($id))
                    )
                )
            );

        return $this->findEntities($qb);

    }//end findByTypeAndId()


    /**
     * Get total count of synchronization contracts
     *
     * @return int Total number of contracts
     */
    public function getTotalCallCount(): int
    {
        // Create query builder
        $qb = $this->db->getQueryBuilder();

        // Build count query
        $qb->select($qb->createFunction('COUNT(*) as count'))
            ->from('openconnector_synchronization_contracts');

        $result = $qb->execute();
        $row    = $result->fetch();

        return (int) $row['count'];

    }//end getTotalCallCount()


    /**
     * Handle object removal by updating or removing associated contracts
     *
     * This method finds all contracts associated with the given object identifier,
     * clears the appropriate fields (origin or target) and deletes contracts that
     * have no remaining associations.
     *
     * @param  string $objectIdentifier The ID of the removed object
     * @return array
     * @throws Exception If there is an error handling the object removal
     */
    public function handleObjectRemoval(string $objectIdentifier): array
    {
        try {
            // Find contracts where object ID matches either origin or target
            $qb = $this->db->getQueryBuilder();
            $qb->select('*')
                ->from('openconnector_synchronization_contracts')
                ->where(
                    $qb->expr()->orX(
                        $qb->expr()->eq('origin_id', $qb->createNamedParameter($objectIdentifier)),
                        $qb->expr()->eq('target_id', $qb->createNamedParameter($objectIdentifier))
                    )
                );

            $contracts = $this->findEntities($qb);

            foreach ($contracts as $contract) {
                // Clear origin fields if object was the source
                if ($contract->getOriginId() === $objectIdentifier) {
                    $contract->setOriginId(null);
                    $contract->setOriginHash(null);
                    $this->update($contract);
                }

                // Clear target fields if object was the target
                if ($contract->getTargetId() === $objectIdentifier) {
                    $contract->setTargetId(null);
                    $contract->setTargetHash(null);
                    $this->update($contract);
                }

                // Delete contract if no remaining associations
                if ($contract->getOriginId() === null && $contract->getTargetId() === null) {
                    $this->delete($contract);
                }
            }

            return $contracts;
        } catch (Exception $e) {
            throw new Exception('Failed to handle object removal: '.$e->getMessage());
        }//end try

    }//end handleObjectRemoval()


}//end class
