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
 * @extends QBMapper<Authentication>
 */
class AuthenticationMapper extends QBMapper {
    /**
     * @var array<string, array<string, mixed>> Configuratie vereisten per type
     */
    private const TYPE_REQUIREMENTS = [
        'apikey' => ['required' => ['key']],
        'jwt' => ['required' => ['token']],
        'username-password' => ['required' => ['username', 'password']],
        'none' => ['required' => []],
        'jwt-HS256' => ['required' => ['secret', 'token']],
        'jwt-vrijbrp' => ['required' => ['secret', 'token', 'issuer']],
        'jwt-pink' => ['required' => ['secret', 'token', 'issuer']],
        'oauth' => ['required' => ['clientId', 'clientSecret', 'authorizationUrl', 'tokenUrl']],
        'certificate' => ['required' => ['certificate', 'privateKey']],
        'saml' => ['required' => ['metadata', 'certificate']],
        'openid-connect' => ['required' => ['clientId', 'clientSecret', 'discoveryUrl']],
        'digest-auth' => ['required' => ['username', 'password', 'realm']],
        'totp' => ['required' => ['secret']],
        'api-hmac' => ['required' => ['key', 'secret']],
        'pkce' => ['required' => ['clientId', 'authorizationUrl']],
        'pki' => ['required' => ['certificate', 'privateKey', 'ca']]
    ];

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'openconnector_auth', Authentication::class);
    }

    /**
     * Vindt een authenticatie configuratie op basis van ID
     * 
     * @param int $id Het ID van de authenticatie configuratie
     * @return Authentication
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function find(int $id): Authentication {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->tableName)
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

        return $this->findEntity($qb);
    }

    /**
     * Haalt alle authenticatie configuraties op
     * 
     * @param int|null $limit Maximum aantal resultaten
     * @param int|null $offset Startpunt van de resultaten
     * @return array<Authentication>
     */
    public function findAll(?int $limit = null, ?int $offset = null): array {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->tableName);

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $this->findEntities($qb);
    }

    /**
     * Maakt een nieuwe authenticatie configuratie aan
     * 
     * @param array<string, mixed> $data De data voor de nieuwe configuratie
     * @throws RuntimeException Als de configuratie niet valide is
     * @return Authentication
     */
    public function createFromArray(array $data): Authentication {
        $this->validateConfiguration($data['type'], $data['configuration'] ?? []);

        $auth = new Authentication();
        $auth->setUuid((string) Uuid::v4());
        $auth->setDateCreated(new \DateTime());
        $auth->setDateModified(new \DateTime());
        
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($auth, $method)) {
                $auth->$method($value);
            }
        }

        return $this->insert($auth);
    }

    /**
     * Valideert de configuratie voor een specifiek type
     * 
     * @param string $type Het type authenticatie
     * @param array<string, mixed> $config De configuratie om te valideren
     * @throws RuntimeException Als de configuratie niet valide is
     */
    private function validateConfiguration(string $type, array $config): void {
        if (!isset(self::TYPE_REQUIREMENTS[$type])) {
            throw new RuntimeException("Ongeldig authenticatie type: $type");
        }

        $requirements = self::TYPE_REQUIREMENTS[$type];
        $missingFields = array_diff($requirements['required'], array_keys($config));
        
        if (!empty($missingFields)) {
            throw new RuntimeException(
                "Ontbrekende verplichte velden voor type $type: " . 
                implode(', ', $missingFields)
            );
        }
    }

    /**
     * Vindt sources die gebruik maken van een specifieke authenticatie
     * 
     * @param int $authId Het ID van de authenticatie configuratie
     * @return array<Source>
     */
    public function findLinkedSources(int $authId): array {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('s.*')
           ->from('openconnector_sources', 's')
           ->where($qb->expr()->eq('s.auth_id', $qb->createNamedParameter($authId, IQueryBuilder::PARAM_INT)));

        return $this->findEntities($qb);
    }
} 