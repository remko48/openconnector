<?php

declare(strict_types=1);

namespace OCA\OpenConnector\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use Symfony\Component\Uid\Uuid;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use RuntimeException;

/**
 * @extends QBMapper<Application>
 */
class ApplicationMapper extends QBMapper {
    /**
     * @var array<string, array<string, mixed>> Configuratie vereisten per type
     */
    private const TYPE_REQUIREMENTS = [
        'api-client' => [
            'required' => ['redirect_uri', 'allowed_scopes'],
            'optional' => ['rate_limit', 'ip_whitelist']
        ],
        'webhook-subscriber' => [
            'required' => ['callback_url', 'events'],
            'optional' => ['secret', 'retry_policy']
        ],
        'oauth-client' => [
            'required' => ['redirect_uri', 'allowed_scopes', 'grant_types'],
            'optional' => ['token_lifetime', 'refresh_token_lifetime']
        ],
    ];

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'openconnector_applications', Application::class);
    }

    /**
     * Vindt een applicatie op basis van ID
     * 
     * @param int $id Het ID van de applicatie
     * @return Application
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function find(int $id): Application {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->tableName)
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

        return $this->findEntity($qb);
    }

    /**
     * Vindt een applicatie op basis van Client ID
     * 
     * @param string $clientId De Client ID van de applicatie
     * @return Application
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findByClientId(string $clientId): Application {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->tableName)
           ->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId)));

        return $this->findEntity($qb);
    }

    /**
     * Haalt alle applicaties op
     * 
     * @param int|null $limit Maximum aantal resultaten
     * @param int|null $offset Startpunt van de resultaten
     * @param array<string, mixed> $filters Optionele filters
     * @return array<Application>
     */
    public function findAll(?int $limit = null, ?int $offset = null, array $filters = []): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->tableName);

        foreach ($filters as $field => $value) {
            if ($value === null) {
                $qb->andWhere($qb->expr()->isNull($field));
            } else {
                $qb->andWhere($qb->expr()->eq($field, $qb->createNamedParameter($value)));
            }
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $this->findEntities($qb);
    }

    /**
     * Maakt een nieuwe applicatie aan
     * 
     * @param array<string, mixed> $data De data voor de nieuwe applicatie
     * @throws RuntimeException Als de configuratie niet valide is
     * @return Application
     */
    public function createFromArray(array $data): Application {
        $this->validateConfiguration($data['type'], $data['configuration'] ?? []);

        $app = new Application();
        $app->setUuid((string) Uuid::v4());
        $app->setClientId((string) Uuid::v4());
        $app->setClientSecret(bin2hex(random_bytes(32)));
        $app->setDateCreated(new \DateTime());
        $app->setDateModified(new \DateTime());
        
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($app, $method)) {
                $app->$method($value);
            }
        }

        return $this->insert($app);
    }

    /**
     * Update een bestaande applicatie
     * 
     * @param int $id ID van de applicatie
     * @param array<string, mixed> $data De nieuwe data
     * @throws RuntimeException Als de configuratie niet valide is
     * @return Application
     */
    public function updateFromArray(int $id, array $data): Application {
        $app = $this->find($id);
        
        if (isset($data['type']) && isset($data['configuration'])) {
            $this->validateConfiguration($data['type'], $data['configuration']);
        }

        $app->setDateModified(new \DateTime());
        
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($app, $method)) {
                $app->$method($value);
            }
        }

        return $this->update($app);
    }

    /**
     * Valideert de configuratie voor een specifiek type
     * 
     * @param string $type Het type applicatie
     * @param array<string, mixed> $config De configuratie om te valideren
     * @throws RuntimeException Als de configuratie niet valide is
     */
    private function validateConfiguration(string $type, array $config): void {
        if (!isset(self::TYPE_REQUIREMENTS[$type])) {
            throw new RuntimeException("Ongeldig applicatie type: $type");
        }

        $requirements = self::TYPE_REQUIREMENTS[$type];
        $missingFields = array_diff($requirements['required'], array_keys($config));
        
        if (!empty($missingFields)) {
            throw new RuntimeException(
                "Ontbrekende verplichte velden voor type $type: " . 
                implode(', ', $missingFields)
            );
        }

        // Check for unknown fields
        $allowedFields = array_merge(
            $requirements['required'], 
            $requirements['optional'] ?? []
        );
        $unknownFields = array_diff(array_keys($config), $allowedFields);
        
        if (!empty($unknownFields)) {
            throw new RuntimeException(
                "Onbekende configuratie velden voor type $type: " . 
                implode(', ', $unknownFields)
            );
        }
    }

    /**
     * Update de laatste toegangstijd van een applicatie
     * 
     * @param int $id ID van de applicatie
     * @return void
     */
    public function updateLastAccess(int $id): void {
        $qb = $this->db->getQueryBuilder();
        
        $qb->update($this->tableName)
           ->set('last_access', $qb->createNamedParameter(new \DateTime(), IQueryBuilder::PARAM_DATE))
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
           ->execute();
    }
} 