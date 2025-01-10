<?php

namespace OCA\OpenConnector\Service;

use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use JetBrains\PhpStorm\NoReturn;
use OCA\OpenRegister\Db\Register;
use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\Schema;
use OCA\OpenRegister\Db\SchemaMapper;
use OCA\OpenRegister\Service\DoesNotExistException;
use OCA\OpenRegister\Service\GuzzleHttp;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IURLGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Service for handling export requests for database entities.
 *
 * This service enables exporting database entities as files in various formats,
 * determined by the `Accept` header of the request. It retrieves the appropriate
 * data from mappers and generates responses or downloadable files.
 */
class ExportService
{
	public function __construct(
		private readonly IURLGenerator $urlGenerator,
		private readonly ObjectService $objectService
	) {}

	/**
	 * Handles an upload api-call to create a new object or update an existing one.
	 *
	 * @param string $objectType The type of object to export.
	 * @param string $id The id of the object to export.
	 * @param string $accept The Accept-header from the export request.
	 *
	 * @return JSONResponse The JSONResponse response.
	 */
	public function export(string $objectType, string $id, string $accept): JSONResponse
	{
		try {
			$mapper = $this->objectService->getMapper(objectType: $objectType);
		} catch (InvalidArgumentException|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
			return new JSONResponse(data: ['error' => "Could not find a mapper for this {type}: " . $objectType], statusCode: 400);
		}

		try {
			$object = $mapper->find($id);
		} catch (Exception $exception) {
			return new JSONResponse(data: ['error' => "Could not find an object with this {id}: ".$id], statusCode: 400);
		}

		$objectArray = $object->jsonSerialize();
		$filename = $objectArray['name'].ucfirst($objectType).'-v'.$objectArray['version'];

		if (str_contains(haystack: $accept, needle: 'application/json') === true) {
			if (empty($objectArray['reference']) === false) {
				$url = $objectArray['reference'];
			} else {
				$url = $objectArray['reference'] = $this->urlGenerator->getAbsoluteURL(url: $this->urlGenerator->linkToRoute(
					routeName: 'openconnector.'.ucfirst($objectType).'s.show',
					arguments: ['id' => $object->getId()])
				);
				unset($objectArray['id'], $objectArray['uuid'], $objectArray['created'], $objectArray['updated'],
					$objectArray['dateCreated'], $objectArray['dateModified']);
				$mapper->updateFromArray(id: $id, object: $objectArray);
			}

			$objArray['@context'] = "http://schema.org";
			$objArray['@type'] = $objectType;
			$objArray['@id'] = $url;
			$objArray = array_merge($objArray, $objectArray);

			// Convert the object data to JSON
			$jsonData = json_encode(value: $objArray, flags: JSON_PRETTY_PRINT);

			$this->downloadJson(jsonData: $jsonData, filename: $filename);
		}

		return new JSONResponse(data: ['error' => "The Accept type $accept is not supported."], statusCode: 400);
	}

	/**
	 * Generate a downloadable json file response.
	 *
	 * @param string $jsonData The json data to create a json file with.
	 * @param string $filename The filename, .json will be added after this filename in this function.
	 *
	 * @return void
	 */
	#[NoReturn] private function downloadJson(string $jsonData, string $filename): void
	{
		// Define the file name and path for the temporary JSON file
		$fileName = $filename.'.json';
		$filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

		// Create and write the JSON data to the file
		file_put_contents(filename: $filePath, data: $jsonData);

		// Set headers to download the file
		header('Content-Type: application/json');
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		header('Content-Length: ' . filesize(filename: $filePath));

		// Output the file contents
		readfile(filename: $filePath);

		// Clean up: delete the temporary file
		unlink(filename: $filePath);
		exit; // Ensure no further script execution
	}
}
