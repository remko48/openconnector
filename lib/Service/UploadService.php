<?php

namespace OCA\OpenConnector\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use OCA\OpenRegister\Db\Register;
use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\Schema;
use OCA\OpenRegister\Db\SchemaMapper;
use OCA\OpenRegister\Service\DoesNotExistException;
use OCA\OpenRegister\Service\GuzzleHttp;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Http\JSONResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Service for handling file and JSON uploads.
 *
 * This service processes uploaded JSON data, either directly via a POST body,
 * from a provided URL, or from an uploaded file. It supports multiple data
 * formats (e.g., JSON, YAML) and integrates with consumers, endpoints, jobs,
 * mappings, sources and synchronizations for database updates.
 */
class UploadService
{
	public function __construct(
		private Client $client,
		private readonly ObjectService $objectService
	) {
		$this->client = new Client([]);
	}

	/**
	 * Handles an upload api-call to create a new object or update an existing one.
	 *
	 * @param array $data The data from the request body to use in creating/updating an object.
	 *
	 * @return JSONResponse The JSONResponse response.
	 * @throws GuzzleException
	 */
	public function upload(array $data): JSONResponse
	{
		// @todo: 1. support file upload instead of taking the json body or url
		// @todo: 2. (To refine) We should create NextCloud files for uploads through OpenCatalogi (If url is posted we should just be able to download and copy the file)

		foreach ($data as $key => $value) {
			if (str_starts_with($key, '_')) {
				unset($data[$key]);
			}
		}

		// Define the allowed keys
		$allowedKeys = ['file', 'url', 'json'];

		// Find which of the allowed keys are in the array
		$matchingKeys = array_intersect_key($data, array_flip($allowedKeys));

		// Check if there is exactly one matching key
		if (count($matchingKeys) === 0) {
			return new JSONResponse(data: ['error' => 'Missing one of these keys in your POST body: file, url or json.'], statusCode: 400);
		}

		if (empty($data['file']) === false) {
			// @todo use .json file content from POST as $phpArray
			return $this->getJSONfromFile();
		}

		if (empty($data['url']) === false && isset($phpArray) === false) {
			return $this->getJSONfromURL($data['url']);
		}

		$phpArray = $data['json'];

		// @todo: ?
//		if (is_string($phpArray) === true) {
//			$phpArray = json_decode($phpArray, associative: true);
//		}
//
//		if ($phpArray === null || $phpArray === false) {
//			return new JSONResponse(data: ['error' => 'Failed to decode JSON input'], statusCode: 400);
//		}

		return $this->saveObject($phpArray);
	}

	/**
	 * Creates or updates an object using the given array as input.
	 *
	 * @param array $phpArray The input php array.
	 *
	 * @return Entity|JSONResponse
	 */
	private function saveObject(array $phpArray): Entity|JSONResponse
	{
		try {
			$mapper = $this->objectService->getMapper(objectType: $phpArray['@type']);
		} catch (InvalidArgumentException|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
			return new JSONResponse(data: ['error' => "Could not find a mapper for this @type: ".$phpArray['@type']], statusCode: 400);
		}

		// Check if object already exists
		if (isset($phpArray['@id']) === true) {
			// @todo: find by using the full url @id instead? ($reference?)
			$explodedId = explode('/', $phpArray['@id']);
			$id = end($explodedId);
			try {
				$mapper->find($id);
			} catch (Exception $exception) {
				// @todo: should we just create a new object in this case?
				return new JSONResponse(data: ['error' => "Could not find an object with this @id: ".$phpArray['@id']], statusCode: 400);
			}

			// @todo:
//			$phpArray['reference'] = $phpArray['@id'];
		}

		unset($phpArray['@context'], $phpArray['@type'], $phpArray['@id']);

		if (isset($id) === true) {
			// @todo: maybe we should do kind of hash comparison here as well?
			$object = $mapper->updateFromArray($id, $phpArray);
			return new JSONResponse(data: ['message' => "Upload successful, updated", 'object' => $object->jsonSerialize()]);
		}

		$object = $mapper->createFromArray($phpArray);
		return new JSONResponse(data: ['message' => "Upload successful, created", 'object' => $object->jsonSerialize()]);
	}

	/**
	 * Gets uploaded file form request and returns it as PHP array to use for creating/updating an object.
	 *
	 * @return array|JSONResponse The file content converted to a PHP array or JSONResponse in case of an error.
	 */
	private function getJSONfromFile(): array|JSONResponse
	{
		// @todo
//		return $this->saveObject(phpArray: $phpArray);

		return new JSONResponse(data: ['error' => 'Not yet implemented'], statusCode: 501);
	}

	/**
	 * Uses Guzzle to call the given URL and returns response as PHP array.
	 *
	 * @param string $url The URL to call.
	 *
	 * @return array|JSONResponse The response from the call converted to PHP array or JSONResponse in case of an error.
	 * @throws GuzzleException
	 */
	private function getJSONfromURL(string $url): array|JSONResponse
	{
		try {
			$response = $this->client->request('GET', $url);
		} catch (GuzzleHttp\Exception\BadResponseException $e) {
			return new JSONResponse(data: ['error' => 'Failed to do a GET api-call on url: '.$url.' '.$e->getMessage()], statusCode: 400);
		}

		$responseBody = $response->getBody()->getContents();

		// Use Content-Type header to determine the format
		$contentType = $response->getHeaderLine('Content-Type');
		switch ($contentType) {
			case 'application/json':
				$phpArray = json_decode(json: $responseBody, associative: true);
				break;
			case 'application/yaml':
				$phpArray = Yaml::parse(input: $responseBody);
				break;
			default:
				// If Content-Type is not specified or not recognized, try to parse as JSON first, then YAML
				$phpArray = json_decode(json: $responseBody, associative: true);
				if ($phpArray === null) {
					$phpArray = Yaml::parse(input: $responseBody);
				}
				break;
		}

		if ($phpArray === null || $phpArray === false) {
			return new JSONResponse(data: ['error' => 'Failed to parse response body as JSON or YAML'], statusCode: 400);
		}

		// @todo:
		// Set reference, might be overwritten if $phpArray has @id set.
//			$phpArray['reference'] = $url;

		return $this->saveObject(phpArray: $phpArray);
	}
}
