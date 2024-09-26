<?php

namespace OCA\OpenConnector\Service;

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

class CallService
{
     /**
     * The constructor sets al needed variables.
     *
     * @param AuthenticationService  $authenticationService The authentication service
     * @param MappingService         $mappingService        The mapping service
     */
    public function __construct(
        AuthenticationService $authenticationService,
        MappingService $mappingService,
        LoggerInterface $callLogger
    ) {
        $this->authenticationService = $authenticationService;
        $this->mappingService        = $mappingService;
        $this->client                = new Client([]);

    }//end __construct() /**

	/**
     * Calls a source according to given configuration.
     *
     * @param Source $source             The source to call.
     * @param string $endpoint           The endpoint on the source to call.
     * @param string $method             The method on which to call the source.
     * @param array  $config             The additional configuration to call the source.
     * @param bool   $asynchronous       Whether or not to call the source asynchronously.
     * @param bool   $createCertificates Whether or not to create certificates for this source.
     *
     * @throws Exception
     *
     * @return Response
     */
    public function call(
        Source $source,
        string $endpoint = '',
        string $method = 'GET',
        array $config = [],
        bool $asynchronous = false,
        bool $createCertificates = true,
        bool $overruleAuth = false
    ): Response 
	{
        $this->source = $source;

		if ($this->source->getIsEnabled() === null || $this->source->getIsEnabled() === false) {
            throw new HttpException('409', "This source is not enabled: {$this->source->getName()}");
        }

        if (empty($this->source->getLocation()) === true) {
            throw new HttpException('409', "This source has no location: {$this->source->getName()}");
        }

		// Check if the source has a configuration and merge it with the given config
        if (empty($this->source->getConfiguration()) === false) {
            $config = array_merge_recursive($config, $this->source->getConfiguration());
        }

		// Check if the config has a Content-Type header and overwrite it if it does
		if (isset($config['headers']['Content-Type']) === true) {
            $overwriteContentType = $config['headers']['Content-Type'];
        }

		// decapiitilized fall back for content-type
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

		// Set the URL to call and add an endpoint if needed	
		$url = $this->source->getLocation().$endpoint;

        // Set authentication if needed. @todo: create  the authentication service
        //$createCertificates && $this->getCertificate($config);

		// Set the request info array
        $requestInfo = [
            'url'    => $url,
            'method' => $method,
        ];

		// Let's log the call.
		$this->source->setLastCall(new \DateTime());
		// @todo: save the source

		// Let's make the call.
		try {
            if ($asynchronous === false) {
                $response = $this->client->request($method, $url, $config);
            } else {
                return $this->client->requestAsync($method, $url, $config);
            }
		} catch (ClientException $e) {
			// @todo: log the error
		}

		return $response;
	}
}
