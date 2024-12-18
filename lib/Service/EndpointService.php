<?php

namespace OCA\OpenConnector\Service;

use Adbar\Dot;
use Exception;
use JWadhams\JsonLogic;
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
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use Psr\Log\LoggerInterface;
use ValueError;

/**
 * Service class for handling endpoint requests
 *
 * This class provides functionality to handle requests to endpoints, either by
 * connecting to a schema within a register or by proxying to a source.
 */
class EndpointService
{

	/**
	 * Constructor for EndpointService
	 *
	 * @param ObjectService $objectService Service for handling object operations
	 * @param CallService $callService Service for making external API calls
	 * @param LoggerInterface $logger Logger interface for error logging
	 */
	public function __construct(
		private readonly ObjectService   $objectService,
		private readonly CallService     $callService,
		private readonly LoggerInterface $logger,
		private readonly IURLGenerator   $urlGenerator,
	)
	{
	}

	/**
	 * Handles incoming requests to endpoints
	 *
	 * This method determines how to handle the request based on the endpoint configuration.
	 * It either routes to a schema within a register or proxies to an external source.
	 *
	 * @param Endpoint $endpoint The endpoint configuration to handle
	 * @param IRequest $request The incoming request object
	 * @return JSONResponse Response containing the result
	 * @throws Exception When endpoint configuration is invalid
	 */
	public function handleRequest(Endpoint $endpoint, IRequest $request, string $path): JSONResponse
	{
		$errors = $this->checkConditions($endpoint, $request);

		if($errors !== []) {
			return new JSONResponse(['error' => 'The following parameters are not correctly set', 'fields' => $errors], 400);
		}

		try {
			// Check if endpoint connects to a schema
			if ($endpoint->getTargetType() === 'register/schema') {
				// Handle CRUD operations via ObjectService
				return $this->handleSchemaRequest($endpoint, $request, $path);
			}

			// Check if endpoint connects to a source
			if ($endpoint->getTargetType() === 'api') {
				// Proxy request to source via CallService
				return $this->handleSourceRequest($endpoint, $request);
			}

			// Invalid endpoint configuration
			throw new Exception('Endpoint must specify either a schema or source connection');

		} catch (Exception $e) {
			$this->logger->error('Error handling endpoint request: ' . $e->getMessage());
			return new JSONResponse(
				['error' => $e->getMessage()],
				400
			);
		}
	}

	/**
	 * Parses a path to get the parameters in a path.
	 *
	 * @param array $endpointArray The endpoint array from an endpoint object.
	 * @param string $path The path called by the client.
	 *
	 * @return array The parsed path with the fields having the correct name.
	 */
	private function getPathParameters(array $endpointArray, string $path): array
	{
		$pathParts = explode(separator: '/', string: $path);

		$endpointArrayNormalized = array_map(
			function ($item) {
				return str_replace(
					search: ['{{', '{{ ', '}}', '}}'],
					replace: '',
					subject: $item
				);
			},
			$endpointArray);

		try {
			$pathParams = array_combine(
				keys: $endpointArrayNormalized,
				values: $pathParts
			);
		} catch (ValueError $error) {
			array_pop($endpointArrayNormalized);
			$pathParams = array_combine(
				keys: $endpointArrayNormalized,
				values: $pathParts
			);
		}

		return $pathParams;
	}

	/**
	 * Fetch objects for the endpoint.
	 *
	 * @param \OCA\OpenRegister\Service\ObjectService|QBMapper $mapper The mapper for the object type
	 * @param array $parameters The parameters from the request
	 * @param array $pathParams The parameters in the path
	 * @param int $status The HTTP status to return.
	 * @return Entity|array The object(s) confirming to the request.
	 * @throws Exception
	 */
	private function getObjects(
		\OCA\OpenRegister\Service\ObjectService|QBMapper $mapper,
		array                                            $parameters,
		array                                            $pathParams,
		int                                              &$status = 200
	): Entity|array
	{
		if (isset($pathParams['id']) === true && $pathParams['id'] === end($pathParams)) {
			return $mapper->find($pathParams['id']);
		} else if (isset($pathParams['id']) === true) {

			// Set the array pointer to the location of the id, so we can fetch the parameters further down the line in order.
			while (prev($pathParams) !== $pathParams['id']) {
			}

			$property = next($pathParams);

			if (next($pathParams) !== false) {
				$id = pos($pathParams);
			}

			$main = $mapper->find($pathParams['id'])->getObject();
			$ids = $main[$property];

			if (isset($id) === true && in_array(needle: $id, haystack: $ids) === true) {

				return $mapper->findSubObjects([$id], $property)[0];
			} else if (isset($id) === true) {
				$status = 404;
				return ['error' => 'not found', 'message' => "the subobject with id $id does not exist"];

			}

			return $mapper->findSubObjects($ids, $property);
		}

		$result = $mapper->findAllPaginated(requestParams: $parameters);

		$returnArray = [
			'count' => $result['total'],
		];

		if ($result['page'] < $result['pages']) {
			$parameters['page'] = $result['page'] + 1;
			$parameters['_path'] = implode('/', $pathParams);

			$returnArray['next'] = $this->urlGenerator->getAbsoluteURL(
				$this->urlGenerator->linkToRoute(
					routeName: 'openconnector.endpoints.handlepath',
					arguments: $parameters
				)
			);
		}
		if ($result['page'] > 1) {
			$parameters['page'] = $result['page'] - 1;
			$parameters['_path'] = implode('/', $pathParams);

			$returnArray['previous'] = $this->urlGenerator->getAbsoluteURL(
				$this->urlGenerator->linkToRoute(
					routeName: 'openconnector.endpoints.handlepath',
					arguments: $parameters
				)
			);
		}

		$returnArray['results'] = $result['results'];

		return $returnArray;
	}

	/**
	 * Handles requests for schema-based endpoints
	 *
	 * @param Endpoint $endpoint The endpoint configuration
	 * @param IRequest $request The incoming request
	 * @return JSONResponse
	 */
	private function handleSchemaRequest(Endpoint $endpoint, IRequest $request, string $path): JSONResponse
	{
		// Get request method
		$method = $request->getMethod();
		$target = explode('/', $endpoint->getTargetId());

		$register = $target[0];
		$schema = $target[1];

		$mapper = $this->objectService->getMapper(schema: $schema, register: $register);

		$parameters = $request->getParams();

		$pathParams = $this->getPathParameters($endpoint->getEndpointArray(), $path);

		unset($parameters['_route'], $parameters['_path']);

		$status = 200;

		// Route to appropriate ObjectService method based on HTTP method
		return match ($method) {
			'GET' => new JSONResponse(
				$this->getObjects(mapper: $mapper, parameters: $parameters, pathParams: $pathParams, status: $status), statusCode: $status
			),
			'POST' => new JSONResponse(
				$mapper->createFromArray(object: $parameters)
			),
			'PUT' => new JSONResponse(
				$mapper->updateFromArray($request->getParams()['id'], $request->getParams(), true, true)
			),
			'DELETE' => new JSONResponse(
				$mapper->delete($request->getParams())
			),
			default => throw new Exception('Unsupported HTTP method')
		};
	}

	/**
	 * Gets the raw content for an http request from the input stream.
	 *
	 * @return string The raw content body for an http request
	 */
	private function getRawContent(): string
	{
		return file_get_contents(filename: 'php://input');
	}

	/**
	 * Get all headers for a HTTP request.
	 *
	 * @param array $server The server data from the request.
	 * @param bool $proxyHeaders Whether the proxy headers should be returned.
	 *
	 * @return array The resulting headers.
	 */
	private function getHeaders(array $server, bool $proxyHeaders = false): array
	{
		$headers = array_filter(
			array: $server,
			callback: function (string $key) use ($proxyHeaders) {
				if (str_starts_with($key, 'HTTP_') === false) {
					return false;
				} else if ($proxyHeaders === false
					&& (str_starts_with(haystack: $key, needle: 'HTTP_X_FORWARDED')
						|| $key === 'HTTP_X_REAL_IP' || $key === 'HTTP_X_ORIGINAL_URI'
					)
				) {
					return false;
				}

				return true;
			},
			mode: ARRAY_FILTER_USE_KEY
		);

		$keys = array_keys($headers);

		return array_combine(
			array_map(
				callback: function ($key) {
					return strtolower(string: substr(string: $key, offset: 5));
				},
				array: $keys),
			$headers
		);
	}

	private function checkConditions(Endpoint $endpoint, IRequest $request): array
	{
		$conditions = $endpoint->getConditions();
		$data['parameters'] = $request->getParams();
		$data['headers'] = $this->getHeaders($request->server, true);

		$result = JsonLogic::apply(logic: $conditions, data: $data);

		if($result === true || $result === []) {
			return [];
		}

		return $result;
	}

	/**
	 * Handles requests for source-based endpoints
	 *
	 * @param Endpoint $endpoint The endpoint configuration
	 * @param IRequest $request The incoming request
	 * @return JSONResponse
	 */
	private function handleSourceRequest(Endpoint $endpoint, IRequest $request): JSONResponse
	{
		$headers = $this->getHeaders($request->server);

		// Proxy the request to the source via CallService
		$response = $this->callService->call(
			source: $endpoint->getSource(),
			endpoint: $endpoint->getPath(),
			method: $request->getMethod(),
			config: [
				'query' => $request->getParams(),
				'headers' => $headers,
				'body' => $this->getRawContent(),
			]
		);

		return new JSONResponse(
			$response->getResponse(),
			$response->getStatusCode()
		);
	}
}
