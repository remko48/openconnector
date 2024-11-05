<?php

namespace OCA\OpenConnector\Service;

use Adbar\Dot;
use Exception;
use OCA\OpenConnector\Db\SourceMapper;
use OCA\OpenConnector\Service\AuthenticationService;
use OCA\OpenConnector\Service\MappingService;
use OCA\OpenConnector\Db\Source;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Response;
use OCA\OpenConnector\Db\CallLog;
use OCA\OpenConnector\Db\CallLogMapper;
use OCA\OpenConnector\Twig\AuthenticationExtension;
use OCA\OpenConnector\Twig\AuthenticationRuntimeLoader;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;

/**
 * This class provides functionality to handle API calls to a specified source within the NextCloud environment.
 *
 * It manages the execution of HTTP requests using the Guzzle HTTP client, while also rendering templates
 * and managing call logs. It utilizes Twig for templating and Guzzle for making HTTP requests, and logs all calls.
 *
 * @todo We should test the effect of @Authors & @Package(s) in Class doc-blocks. And add them if possible.
 */
class CallService
{
	private Client $client;
	private Environment $twig;

	/**
	 * The constructor sets al needed variables.
	 *
	 * @param CallLogMapper $callLogMapper
	 * @param ArrayLoader $loader
	 * @param AuthenticationService $authenticationService
	 */
	public function __construct(
		private readonly CallLogMapper $callLogMapper,
		ArrayLoader $loader,
		AuthenticationService $authenticationService
	)
	{
		$this->client = new Client([]);
		$this->twig = new Environment($loader);
		$this->twig->addExtension(new AuthenticationExtension());
		$this->twig->addRuntimeLoader(new AuthenticationRuntimeLoader($authenticationService));
	}

	/**
	 * Renders a value using Twig templating if the value contains template syntax.
	 * If the value is an array, recursively renders each element.
	 *
	 * @param array|string $value The value to render, which can be a string or an array.
	 * @param Source $source The source object used as context for rendering templates.
	 *
	 * @return array|string The rendered value, either as a processed string or an array.
	 * @throws LoaderError If there is an error loading a Twig template.
	 * @throws SyntaxError If there is a syntax error in a Twig template.
	 */
	private function renderValue(array|string $value, Source $source): array|string
	{
			if (is_array($value) === false
				&& str_contains(haystack: $value, needle: "{{") === true
				&& str_contains(haystack: $value, needle: "}}") === true
			) {
				return $this->twig->createTemplate(template: $value, name: "sourceConfig")->render(context: ['source' => $source]);
			} else if (is_array($value) === true){
				$value = array_map(function($value) use ($source) { return $this->renderValue($value, $source);}, $value);
			}

			return $value;
	}

	/**
	 * Renders configuration values using Twig templating, applying the provided source as context.
	 * Recursively processes all values in the configuration array.
	 *
	 * @param array $configuration The configuration array to render.
	 * @param Source $source The source object used as context for rendering templates.
	 *
	 * @return array The rendered configuration array.
	 * @throws LoaderError If there is an error loading a Twig template.
	 * @throws SyntaxError If there is a syntax error in a Twig template.
	 */
	private function renderConfiguration(array $configuration, Source $source): array
	{
		return array_map(function($value) use ($source) { return $this->renderValue($value, $source);}, $configuration);
	}

	/**
	 * Calls a source according to given configuration.
	 *
	 * @param Source $source The source to call.
	 * @param string $endpoint The endpoint on the source to call.
	 * @param string $method The method on which to call the source.
	 * @param array $config The additional configuration to call the source.
	 * @param bool $asynchronous Whether to call the source asynchronously.
	 * @param bool $createCertificates Whether to create certificates for this source.
	 * @param bool $overruleAuth ???
	 *
	 * @return CallLog
	 * @throws GuzzleException
	 * @throws \OCP\DB\Exception
	 */
	public function call(
		Source $source,
		string $endpoint = '',
		string $method = 'GET',
		array $config = [],
		bool $asynchronous = false,
		bool $createCertificates = true,
		bool $overruleAuth = false
	): CallLog
	{
		$this->source = $source;

		if ($this->source->getIsEnabled() === null || $this->source->getIsEnabled() === false) {
			// Create and save the CallLog
			$callLog = new CallLog();
			$callLog->setUuid(Uuid::v4());
			$callLog->setSourceId($this->source->getId());
			$callLog->setStatusCode(409);
			$callLog->setStatusMessage("This source is not enabled");
			$callLog->setCreated(new \DateTime());
			$callLog->setUpdated(new \DateTime());

			$this->callLogMapper->insert($callLog);

			return $callLog;
		}

		if (empty($this->source->getLocation()) === true) {
			// Create and save the CallLog
			$callLog = new CallLog();
			$callLog->setUuid(Uuid::v4());
			$callLog->setSourceId($this->source->getId());
			$callLog->setStatusCode(409);
			$callLog->setStatusMessage("This source has no location");
			$callLog->setCreated(new \DateTime());
			$callLog->setUpdated(new \DateTime());

			$this->callLogMapper->insert($callLog);

			return $callLog;
		}

		// Check if the source has a configuration and merge it with the given config
		if (empty($this->source->getConfiguration()) === false) {
			$config = array_merge_recursive($config, $this->applyConfigDot($this->source->getConfiguration()));
		}

		// Check if the config has a Content-Type header and overwrite it if it does
		if (isset($config['headers']['Content-Type']) === true) {
			$overwriteContentType = $config['headers']['Content-Type'];
		}

		// decapitalized fall back for content-type
		if (isset($config['headers']['content-type']) === true) {
			$overwriteContentType = $config['headers']['content-type'];
		}

		// Make sure we do not have an array of accept headers but just one value
		if (isset($config['headers']['accept']) === true && is_array($config['headers']['accept']) === true) {
			$config['headers']['accept'] = $config['headers']['accept'][0];
		}


		// Check if the config has a headers array and create it if it doesn't
		if (isset($config['headers']) === false) {
			$config['headers'] = [];
		}

		// We want to surpress guzzle exceptions and return the response instead
		$config['http_errors'] = false;

		$config = $this->renderConfiguration(configuration: $config, source: $source);

		// Set the URL to call and add an endpoint if needed
		$url = $this->source->getLocation().$endpoint;

		// Set authentication if needed. @todo: create  the authentication service
		//$createCertificates && $this->getCertificate($config);

		// Let's log the call.
		$this->source->setLastCall(new \DateTime());
		// @todo: save the source
		// Let's make the call.
		$time_start = microtime(true);
		try {
			if ($asynchronous === false) {
			   $response = $this->client->request($method, $url, $config);
			} else {
				return $this->client->requestAsync($method, $url, $config);
			}
		} catch (GuzzleHttp\Exception\BadResponseException $e) {
			$response = $e->getResponse();
		}

		$time_end = microtime(true);

		// Let create the data array
		$data = [
			'request' => [
				'url' => $url,
				'method' => $method,
				...$config
			],
			'response' => [
				'statusCode' => $response->getStatusCode(),
				'statusMessage' => $response->getReasonPhrase(),
				'responseTime' => ( $time_end - $time_start ) * 1000,
				'size' => $response->getBody()->getSize(),
				'remoteIp' => $response->getHeaderLine('X-Real-IP') ?: $response->getHeaderLine('X-Forwarded-For') ?: null,
				'headers' => $response->getHeaders(),
				'body' => $response->getBody()->getContents(),
			]
		];

		// Create and save the CallLog
		$callLog = new CallLog();
		$callLog->setUuid(Uuid::v4());
		$callLog->setSourceId($this->source->getId());
		$callLog->setStatusCode($data['response']['statusCode']);
		$callLog->setStatusMessage($data['response']['statusMessage']);
		$callLog->setRequest($data['request']);
		$callLog->setResponse($data['response']);
		$callLog->setCreated(new \DateTime());

		$this->callLogMapper->insert($callLog);

		return $callLog;
	}

	/**
	 * Uses Adbar Dot to place the values of keys with a dot in it in the $config array
	 * to the correct position in the then updated multidimensional $config array.
	 *
	 * @param array $config The config array.
	 *
	 * @return array The updated config array.
	 */
	private function applyConfigDot(array $config): array
	{
		$dotConfig = new Dot($config);
		$unsetKeys = [];

		// Check if there are keys containing a dot we want to map to a different position in the $config array.
		foreach ($config as $key => $value) {
			if (str_contains($key, '.')) {
				$dotConfig->set($key, $value);
				$unsetKeys[] = $key;
			}
		}

		// Remove the old keys containing a dot that we mapped to a different position in the $config array.
		$config = $dotConfig->all();
		foreach ($unsetKeys as $key) {
			unset($config[$key]);
		}

		return $config;
	}
}
