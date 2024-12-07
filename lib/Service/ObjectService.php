<?php

namespace OCA\OpenConnector\Service;

use Adbar\Dot;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use OCP\App\IAppManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Uid\Uuid;

class ObjectService
{

	public const BASE_OBJECT = [
		'database'   => 'objects',
		'collection' => 'json',
	];

	public function __construct(
		private readonly IAppManager $appManager,
		private readonly ContainerInterface $container,
	)
	{

	}

	/**
	 * Gets a guzzle client based upon given config.
	 *
	 * @param array $config The config to be used for the client.
	 * @return Client
	 */
	public function getClient(array $config): Client
	{
		$guzzleConf = $config;
		unset($guzzleConf['mongodbCluster']);

		return new Client($config);
	}

	/**
	 * Save an object to MongoDB
	 *
	 * @param array $data	The data to be saved.
	 * @param array $config The configuration that should be used by the call.
	 *
	 * @return array The resulting object.
	 * @throws GuzzleException
	 */
	public function saveObject(array $data, array $config): array
	{
		$client = $this->getClient(config: $config);

		$object 			      = self::BASE_OBJECT;
		$object['dataSource']     = $config['mongodbCluster'];
		$object['document']       = $data;
		$object['document']['id'] = $object['document']['_id'] = Uuid::v4();

		$result = $client->post(
			uri: 'action/insertOne',
			options: ['json' => $object],
		);
		$resultData =  json_decode(
			json: $result->getBody()->getContents(),
			associative: true
		);
		$id = $resultData['insertedId'];

		return $this->findObject(filters: ['_id' => $id], config: $config);
	}

	/**
	 * Finds objects based upon a set of filters.
	 *
	 * @param array $filters The filters to compare the object to.
	 * @param array $config  The configuration that should be used by the call.
	 *
	 * @return array The objects found for given filters.
	 *
	 * @throws GuzzleException
	 */
	public function findObjects(array $filters, array $config): array
	{
		$client = $this->getClient(config: $config);

		$object               = self::BASE_OBJECT;
		$object['dataSource'] = $config['mongodbCluster'];
		$object['filter']     = $filters;

		// @todo Fix mongodb sort
		// if (empty($sort) === false) {
		// 	$object['filter'][] = ['$sort' => $sort];
		// }

		$returnData = $client->post(
			uri: 'action/find',
			options: ['json' => $object]
		);

		return json_decode(
			json: $returnData->getBody()->getContents(),
			associative: true
		);
	}

	/**
	 * Finds an object based upon a set of filters (usually the id)
	 *
	 * @param array $filters The filters to compare the objects to.
	 * @param array $config  The config to be used by the call.
	 *
	 * @return array The resulting object.
	 *
	 * @throws GuzzleException
	 */
	public function findObject(array $filters, array $config): array
	{
		$client = $this->getClient(config: $config);

		$object               = self::BASE_OBJECT;
		$object['filter']     = $filters;
		$object['dataSource'] = $config['mongodbCluster'];

		$returnData = $client->post(
			uri: 'action/findOne',
			options: ['json' => $object]
		);

		$result = json_decode(
			json: $returnData->getBody()->getContents(),
			associative: true
		);

		return $result['document'];
	}



	/**
	 * Updates an object in MongoDB
	 *
	 * @param array $filters The filter to search the object with (id)
	 * @param array $update  The fields that should be updated.
	 * @param array $config  The configuration to be used by the call.
	 *
	 * @return array The updated object.
	 *
	 * @throws GuzzleException
	 */
	public function updateObject(array $filters, array $update, array $config): array
	{
		$client = $this->getClient(config: $config);

		$dotUpdate = new Dot($update);

		$object                   = self::BASE_OBJECT;
		$object['filter']         = $filters;
		$object['update']['$set'] = $update;
		$object['upsert']		  = true;
		$object['dataSource']     = $config['mongodbCluster'];



			$returnData = $client->post(
				uri: 'action/updateOne',
				options: ['json' => $object]
			);

		return $this->findObject($filters, $config);
	}

	/**
	 * Delete an object according to a filter (id specifically)
	 *
	 * @param array $filters The filters to use.
	 * @param array $config  The config to be used by the call.
	 *
	 * @return array An empty array.
	 *
	 * @throws GuzzleException
	 */
	public function deleteObject(array $filters, array $config): array
	{
		$client = $this->getClient(config: $config);

		$object                   = self::BASE_OBJECT;
		$object['filter']         = $filters;
		$object['dataSource']     = $config['mongodbCluster'];

		$returnData = $client->post(
			uri: 'action/deleteOne',
			options: ['json' => $object]
		);

		return [];
	}

	/**
	 * Aggregates objects for search facets.
	 *
	 * @param array $filters  The filters apply to the search request.
	 * @param array $pipeline The pipeline to use.
	 * @param array $config   The configuration to use in the call.
	 * @return array
	 * @throws GuzzleException
	 */
	public function aggregateObjects(array $filters, array $pipeline, array $config):array
	{
		$client = $this->getClient(config: $config);

		$object               = self::BASE_OBJECT;
		$object['filter']     = $filters;
		$object['pipeline']   = $pipeline;
		$object['dataSource'] = $config['mongodbCluster'];

		$returnData = $client->post(
			uri: 'action/aggregate',
			options: ['json' => $object]
		);

		return json_decode(
			json: $returnData->getBody()->getContents(),
			associative: true
		);

	}

	/**
	 * Attempts to retrieve the OpenRegister service from the container.
	 *
	 * @return mixed|null The OpenRegister service if available, null otherwise.
	 * @throws ContainerExceptionInterface|NotFoundExceptionInterface
	 */
	public function getOpenRegisters(): ?\OCA\OpenRegister\Service\ObjectService
	{
		if (in_array(needle: 'openregister', haystack: $this->appManager->getInstalledApps()) === true) {
			try {
				// Attempt to get the OpenRegister service from the container
				return $this->container->get('OCA\OpenRegister\Service\ObjectService');
			} catch (Exception $e) {
				// If the service is not available, return null
				return null;
			}
		}

		return null;
	}

}
