<?php

/**
 * Service for processing rules in synchronization contexts.
 *
 * @category  Service
 * @package   OpenConnector
 * @author    Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (C) 2024 Conduction B.V. All rights reserved.
 * @license   EUPL 1.2
 * @version   GIT: <git_id>
 * @link      https://openregister.app
 *
 * @since 1.0.0 - Initial creation of the RuleProcessorService class
 */

namespace OCA\OpenConnector\Service;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JWadhams\JsonLogic;
use OCA\OpenConnector\Db\Rule;
use OCA\OpenConnector\Db\RuleMapper;
use OCA\OpenConnector\Db\Source;
use OCA\OpenConnector\Db\Synchronization;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\GenericFileException;
use OCP\Files\LockedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Adbar\Dot;

/**
 * Service for processing rules in synchronization contexts.
 *
 * This class handles evaluation of rules and execution of rule actions.
 *
 * @category  Service
 * @package   OpenConnector
 * @author    Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (C) 2024 Conduction B.V. All rights reserved.
 * @license   EUPL 1.2
 * @version   GIT: <git_id>
 * @link      https://openregister.app
 *
 * @since 1.0.0 - Initial creation of the RuleProcessorService class
 */
class RuleProcessorService
{

    /**
     * The rule mapper instance.
     *
     * @var RuleMapper
     */
    private readonly RuleMapper $ruleMapper;

    /**
     * The mapping service instance.
     *
     * @var MappingService
     */
    private readonly MappingService $mappingService;

    /**
     * The file handler service instance.
     *
     * @var FileHandlerService
     */
    private readonly FileHandlerService $fileHandlerService;


    /**
     * Constructor.
     *
     * @param RuleMapper         $ruleMapper         The rule mapper instance
     * @param MappingService     $mappingService     The mapping service instance
     * @param FileHandlerService $fileHandlerService The file handler service instance
     */
    public function __construct(
        RuleMapper $ruleMapper,
        MappingService $mappingService,
        FileHandlerService $fileHandlerService
    ) {
        $this->ruleMapper         = $ruleMapper;
        $this->mappingService     = $mappingService;
        $this->fileHandlerService = $fileHandlerService;
    }


    /**
     * Processes rules for an endpoint request.
     *
     * @param Synchronization $synchronization The endpoint being processed
     * @param array           $data            Current request data
     * @param string          $timing          The timing (before or after)
     * @param string|null     $objectId        Optional object ID
     * @param int|null        $registerId      Optional register ID
     * @param int|null        $schemaId        Optional schema ID
     *
     * @return array|JSONResponse Returns modified data or error response if rule fails
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws GuzzleException
     * @throws Exception
     */
    public function processRules(
        Synchronization $synchronization,
        array $data,
        string $timing,
        ?string $objectId = null,
        ?int $registerId = null,
        ?int $schemaId = null
    ): array | JSONResponse {
        $rules = $synchronization->getActions();
        if (empty($rules)) {
            return $data;
        }

        try {
            // Get all rules at once and sort by order
            $ruleEntities = array_filter(
                array_map(
                    fn($ruleId) => $this->getRuleById($ruleId),
                    $rules
                )
            );

            // Sort rules by order
            usort($ruleEntities, fn($a, $b) => ($a->getOrder() - $b->getOrder()));

            // Process each rule in order
            foreach ($ruleEntities as $rule) {
                // Check rule conditions
                if (!$this->checkRuleConditions($rule, $data) || $rule->getTiming() !== $timing) {
                    continue;
                }

                // Process rule based on type
                $result = match ($rule->getType()) {
                'error' => $this->processErrorRule($rule),
                'mapping' => $this->processMappingRule($rule, $data),
                'synchronization' => $this->processSyncRule($rule, $data),
                'fetch_file' => $this->processFetchFileRule($rule, $data, $objectId),
                'write_file' => $this->processWriteFileRule($rule, $data, $objectId, $registerId, $schemaId),
                default => throw new Exception('Unsupported rule type: ' . $rule->getType()),
                };

                    // If result is JSONResponse, return error immediately
                    if ($result instanceof JSONResponse) {
                        return $result;
                    }

                    // Update data with rule result
                    $data = $result;
            }//end foreach

            return $data;
        } catch (Exception $e) {
            return new JSONResponse(['error' => 'Rule processing failed: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Get a rule by its ID using RuleMapper.
     *
     * @param string $id The unique identifier of the rule
     *
     * @return Rule|null The rule object if found, or null if not found
     */
    private function getRuleById(string $id): ?Rule
    {
        try {
            return $this->ruleMapper->find((int) $id);
        } catch (Exception $e) {
            return null;
        }
    }


    /**
     * Check if rule conditions are met for given data.
     *
     * @param Rule                $rule The rule containing conditions
     * @param array<string,mixed> $data The data to evaluate conditions against
     *
     * @return bool True if conditions are met, false otherwise
     */
    private function checkRuleConditions(Rule $rule, array $data): bool
    {
        $conditions = $rule->getConditions();

        // Return true if no conditions specified
        if (empty($conditions)) {
            return true;
        }

        return JsonLogic::apply($conditions, $data) === true;
    }


    /**
     * Process an error rule and return an error response.
     *
     * @param Rule $rule The rule containing error configuration
     *
     * @return JSONResponse The error response with details and status code
     */
    private function processErrorRule(Rule $rule): JSONResponse
    {
        $config = $rule->getConfiguration();
        return new JSONResponse(
            [
            'error'   => $config['error']['name'],
            'message' => $config['error']['message'],
            ],
            $config['error']['code']
        );
    }


    /**
     * Process a mapping rule to transform data.
     *
     * @param Rule                $rule The rule containing mapping configuration
     * @param array<string,mixed> $data The data to transform
     *
     * @return array<string,mixed> The transformed data
     *
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function processMappingRule(Rule $rule, array $data): array
    {
        $config  = $rule->getConfiguration();
        $mapping = $this->mappingService->getMapping($config['mapping']);

        return $this->mappingService->executeMapping($mapping, $data);
    }


    /**
     * Process a synchronization rule.
     *
     * @param Rule                $rule The rule containing sync configuration
     * @param array<string,mixed> $data The data to synchronize
     *
     * @return array<string,mixed> The synchronized data
     */
    private function processSyncRule(Rule $rule, array $data): array
    {
        // Implementation would be added based on application needs
        return $data;
    }


    /**
     * Process a rule to fetch a file from external source.
     *
     * @param Rule                $rule     The rule to process
     * @param array<string,mixed> $data     The object data
     * @param string|null         $objectId The object ID
     *
     * @return array<string,mixed> The updated object data
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws GenericFileException
     * @throws LockedException
     * @throws GuzzleException
     * @throws LoaderError
     * @throws SyntaxError
     * @throws \OCP\DB\Exception
     * @throws Exception
     */
    private function processFetchFileRule(
        Rule $rule,
        array $data,
        ?string $objectId
    ): array {
        if ($objectId === null) {
            return $data;
        }

        if (!isset($rule->getConfiguration()['fetch_file'])) {
            throw new Exception('No configuration found for fetch_file');
        }

        $config = $rule->getConfiguration()['fetch_file'];
        $source = $this->getSourceById($config['source']);

        // Get file path from data
        $dataDot  = new Dot($data);
        $endpoint = $dataDot[$config['filePath']];

        if ($endpoint === null) {
            return $dataDot->jsonSerialize();
        }

        // Process single or multiple endpoints
        if (is_array($endpoint)) {
            $result = [];
            foreach ($endpoint as $key => $value) {
                // Extract tags and filename
                $tags     = [];
                $filename = null;

                if (is_array($value)) {
                    $endpointUrl = $value['endpoint'];

                    // Add label as tag if configured
                    if (
                        isset($value['label'])
                        && isset($config['tags'])
                        && in_array($value['label'], $config['tags'])
                    ) {
                        $tags = [$value['label']];
                    }

                    if (isset($value['filename'])) {
                        $filename = $value['filename'];
                    }
                } else {
                    $endpointUrl = $value;
                }

                $result[$key] = $this->fileHandlerService->fetchFile(
                    source: $source,
                    endpoint: $endpointUrl,
                    config: $config,
                    objectId: $objectId,
                    tags: $tags,
                    filename: $filename
                );
            }//end foreach

            $dataDot[$config['filePath']] = $result;
        } else {
            $dataDot[$config['filePath']] = $this->fileHandlerService->fetchFile(
                source: $source,
                endpoint: $endpoint,
                config: $config,
                objectId: $objectId
            );
        }//end if

        return $dataDot->jsonSerialize();
    }


    /**
     * Process a rule to write files.
     *
     * @param Rule        $rule       The rule to process
     * @param array       $data       The data to write
     * @param string|null $objectId   The object to write the data to
     * @param int|null    $registerId The register the object is in
     * @param int|null    $schemaId   The schema the object is in
     *
     * @return array The updated object data
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function processWriteFileRule(
        Rule $rule,
        array $data,
        ?string $objectId,
        ?int $registerId,
        ?int $schemaId
    ): array {
        if ($objectId === null || $registerId === null || $schemaId === null) {
            return $data;
        }

        if (!isset($rule->getConfiguration()['write_file'])) {
            throw new Exception('No configuration found for write_file');
        }

        // Implementation would be specific to file processing requirements
        // and would use the FileHandlerService
        return $data;
    }


    /**
     * Gets a source by ID.
     *
     * This is a placeholder method that should be implemented in the actual service.
     *
     * @param string $sourceId The ID of the source
     *
     * @return Source The source object
     *
     * @throws Exception If source not found
     */
    private function getSourceById(string $sourceId): Source
    {
        // This would need to be implemented based on the application's data access methods
        throw new Exception('Method not implemented');
    }
}
