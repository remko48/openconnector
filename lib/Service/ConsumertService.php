<?php

namespace OCA\OpenConnector\Service;

use DateTime;
use Exception;
use OCA\OpenConnector\Db\Consumer;
use OCA\OpenConnector\Db\ConsumerMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Service class for managing consumers
 *
 * @package OCA\OpenConnector\Service
 */
class ConsumerService {

    /**
     * @param ConsumerMapper $mapper
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ConsumerMapper $mapper,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Establishes a new consumer
     *
     * @param array $data Consumer data
     * @param string $userId Current user ID
     * @return Consumer
     * @throws Exception
     *
     * @psalm-param array{
     *     name: string,
     *     description?: string,
     *     domains?: array,
     *     ips?: array,
     *     authorizationType?: string,
     *     authorizationConfiguration?: string
     * } $data
     */
    public function establishConsumer(array $data, string $userId): Consumer {
        try {
            // Validate required fields
            if (empty($data['name'])) {
                throw new Exception('Consumer name is required');
            }

            // Create new consumer entity
            $consumer = new Consumer();
            $consumer->setUuid(Uuid::v4()->toString());
            $consumer->setName($data['name']);
            $consumer->setDescription($data['description'] ?? '');
            $consumer->setDomains($data['domains'] ?? []);
            $consumer->setIps($data['ips'] ?? []);
            $consumer->setAuthorizationType($data['authorizationType'] ?? 'none');
            $consumer->setAuthorizationConfiguration($data['authorizationConfiguration'] ?? null);
            $consumer->setUserId($userId);
            $consumer->setCreated(new DateTime());
            $consumer->setUpdated(new DateTime());

            // Persist the consumer
            return $this->mapper->insert($consumer);

        } catch (Exception $e) {
            $this->logger->error('Failed to establish consumer: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $data
            ]);
            throw new Exception('Failed to establish consumer: ' . $e->getMessage());
        }
    }

    /**
     * Get a consumer by its UUID
     *
     * @param string $uuid
     * @param string $userId
     * @return Consumer
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function getConsumerByUuid(string $uuid, string $userId): Consumer {
        return $this->mapper->findAll(filters: [
            'uuid' => $uuid,
            'userId' => $userId
        ])[0] ?? throw new DoesNotExistException('Consumer not found');
    }

    /**
     * Get all consumers for a user
     *
     * @param string $userId
     * @param array|null $filters Additional filters
     * @return Consumer[]
     */
    public function getUserConsumers(string $userId, ?array $filters = []): array {
        $filters['userId'] = $userId;
        return $this->mapper->findAll(filters: $filters);
    }

    /**
     * Delete a consumer
     *
     * @param string $uuid
     * @param string $userId
     * @return void
     * @throws DoesNotExistException
     * @throws Exception
     */
    public function deleteConsumer(string $uuid, string $userId): void {
        try {
            $consumer = $this->getConsumerByUuid($uuid, $userId);
            $this->mapper->delete($consumer);
        } catch (Exception $e) {
            $this->logger->error('Failed to delete consumer: ' . $e->getMessage(), [
                'exception' => $e,
                'uuid' => $uuid
            ]);
            throw new Exception('Failed to delete consumer: ' . $e->getMessage());
        }
    }
}
