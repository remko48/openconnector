<?php

namespace OCA\OpenConnector\Service;

use Adbar\Dot;
use Exception;
use OCA\OpenConnector\Db\SourceMapper;
use OCA\OpenConnector\Service\AuthenticationService;
use OCA\OpenConnector\Service\MappingService;
use OCA\OpenConnector\Service\ObjectService;
use OCA\OpenConnector\Service\CallService;
use OCA\OpenConnector\Db\Source;
use OCA\OpenConnector\Db\Endpoint;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

/**
 * Service class for handling endpoint requests
 *
 * This class provides functionality to handle requests to endpoints, either by
 * connecting to a schema within a register or by proxying to a source.
 */
class EndpointService {

    /**
     * Constructor for EndpointService
     *
     * @param ObjectService $objectService Service for handling object operations
     * @param CallService $callService Service for making external API calls
     * @param LoggerInterface $logger Logger interface for error logging
     */
    public function __construct(
        private readonly ObjectService $objectService,
        private readonly CallService $callService,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Handles incoming requests to endpoints
     *
     * This method determines how to handle the request based on the endpoint configuration.
     * It either routes to a schema within a register or proxies to an external source.
     *
     * @param Endpoint $endpoint The endpoint configuration to handle
     * @param IRequest $request The incoming request object
     * @return \OCP\AppFramework\Http\JSONResponse Response containing the result
     * @throws \Exception When endpoint configuration is invalid
     */
    public function handleRequest(Endpoint $endpoint, IRequest $request): \OCP\AppFramework\Http\JSONResponse {
        try {
            // Check if endpoint connects to a schema
            if ($endpoint->getSchema() !== null) {
                // Handle CRUD operations via ObjectService
                return $this->handleSchemaRequest($endpoint, $request);
            }
            
            // Check if endpoint connects to a source
            if ($endpoint->getSource() !== null) {
                // Proxy request to source via CallService
                return $this->handleSourceRequest($endpoint, $request);
            }

            // Invalid endpoint configuration
            throw new \Exception('Endpoint must specify either a schema or source connection');

        } catch (\Exception $e) {
            $this->logger->error('Error handling endpoint request: ' . $e->getMessage());
            return new \OCP\AppFramework\Http\JSONResponse(
                ['error' => $e->getMessage()],
                400
            );
        }
    }

    /**
     * Handles requests for schema-based endpoints
     * 
     * @param Endpoint $endpoint The endpoint configuration
     * @param IRequest $request The incoming request
     * @return \OCP\AppFramework\Http\JSONResponse
     */
    private function handleSchemaRequest(Endpoint $endpoint, IRequest $request): \OCP\AppFramework\Http\JSONResponse {
        // Get request method
        $method = $request->getMethod();
        
        // Route to appropriate ObjectService method based on HTTP method
        return match($method) {
            'GET' => new \OCP\AppFramework\Http\JSONResponse(
                $this->objectService->get($endpoint->getSchema(), $request->getParams())
            ),
            'POST' => new \OCP\AppFramework\Http\JSONResponse(
                $this->objectService->create($endpoint->getSchema(), $request->getParams())
            ),
            'PUT' => new \OCP\AppFramework\Http\JSONResponse(
                $this->objectService->update($endpoint->getSchema(), $request->getParams())
            ),
            'DELETE' => new \OCP\AppFramework\Http\JSONResponse(
                $this->objectService->delete($endpoint->getSchema(), $request->getParams())
            ),
            default => throw new \Exception('Unsupported HTTP method')
        };
    }

    /**
     * Handles requests for source-based endpoints
     *
     * @param Endpoint $endpoint The endpoint configuration
     * @param IRequest $request The incoming request
     * @return \OCP\AppFramework\Http\JSONResponse
     */
    private function handleSourceRequest(Endpoint $endpoint, IRequest $request): \OCP\AppFramework\Http\JSONResponse {
        // Proxy the request to the source via CallService
        $response = $this->callService->call(
            source: $endpoint->getSource(),
            endpoint: $endpoint->getPath(),
            method: $request->getMethod(),
            config: [
                'query' => $request->getParams(),
                'headers' => $request->getHeaders(),
                'body' => $request->getContent()
            ]
        );

        return new \OCP\AppFramework\Http\JSONResponse(
            $response->getResponse(),
            $response->getStatusCode()
        );
    }
}
