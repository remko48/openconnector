<?php

namespace OCA\OpenConnector\Service;


use DateTime;
use GuzzleHttp\Client;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\HS384;
use Jose\Component\Signature\Algorithm\HS512;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Algorithm\RS384;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use OAuthException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class AuthenticationService
{

	public const REQUIRED_PARAMETERS_CLIENT_CREDENTIALS = [
		'grant_type',
		'scope',
		'authentication',
		'client_id',
		'client_secret',
	];
	public const REQUIRED_PARAMETERS_PASSWORD = [
		'grant_type',
		'scope',
		'authentication',
		'username',
		'password',
	];

	public const REQUIRED_PARAMETERS_JWT = [
		'payload',
		'secret',
		'algorithm'
	];

	public function __construct(
		ArrayLoader $loader
	)
	{
		$this->twig = new Environment(loader: $loader);
	}

	/**
	 * Create call options for OAuth with Client Credentials
	 *
	 * @param array $configuration Configuration array for authentication.
	 *
	 * @return array|array[] The call options for OAuth with Client Credentials.
	 */
	private function createClientCredentialConfig(array $configuration): array
	{
		if(($diff = array_diff(self::REQUIRED_PARAMETERS_CLIENT_CREDENTIALS, array_keys(array: $configuration))) !== []) {
			throw new BadRequestException(message: 'Some required parameters are not set: ['.implode(separator: ',', array: $diff).']');
		}

		$callConfig = [
			'form_params' => [
				'grant_type' => $configuration['grant_type'],
				'scope'		 => $configuration['scope'],
			]
		];

		if($configuration['authentication'] === 'body') {
			$callConfig['form_params']['client_id']     = $configuration['client_id'];
			$callConfig['form_params']['client_secret'] = $configuration['client_secret'];
		} else if ($configuration['authentication'] === 'basic_auth') {
			$callConfig['auth'] = [
				'username' => $configuration['client_id'],
				'password' => $configuration['client_secret'],
			];
		}
		//@todo: check for off-cases, i.e. camelCase (not according to OAuth standards)

		return $callConfig;
	}

	/**
	 * Create call options for OAuth with Password Credentials
	 *
	 * @param array $configuration Configuration array for authentication.
	 *
	 * @return array|array[] The call options for OAuth with Password Credentials
	 */
	private function createPasswordConfig(array $configuration): array
	{

		if(($diff = array_diff(self::REQUIRED_PARAMETERS_PASSWORD, array_keys(array: $configuration))) !== []) {
			throw new BadRequestException(message: 'Some required parameters are not set: ['.implode(separator: ',', array: $diff).']');
		}

		$callConfig = [
			'form_params' => [
				'grant_type' => $configuration['grant_type'],
				'scope'		 => $configuration['scope'],
			]
		];

		if($configuration['authentication'] === 'body') {
			$callConfig['form_params']['username'] = $configuration['username'];
			$callConfig['form_params']['password'] = $configuration['password'];
		} else if ($configuration['authentication'] === 'basic_auth') {
			$callConfig['auth'] = [
				'username' => $configuration['username'],
				'password' => $configuration['password'],
			];
		}


		return $callConfig;
	}

	/**
	 * Requests an OAuth Access Token with predefined configuration
	 *
	 * @param string $endpoint	   The OAuth token endpoint.
	 * @param array $configuration The configuration for the OAuth call.

	 * @return string The resulting access token
	 *
	 * @throws BadRequestException					 Thrown if the configuration is not compatible with OAuth.
	 * @throws \GuzzleHttp\Exception\GuzzleException Thrown if the token endpoint does not respond with an access token.
	 * @todo Convert GuzzleException to another error.
	 */
    public function fetchOAuthTokens (string $endpoint, array $configuration): string
	{
		if (isset($configuration['grant_type']) === false) {
			throw new BadRequestException(message: 'Grant type not set, cannot request token');
		}

		switch ($configuration['grant_type'])
		{
			case 'client_credentials':
				$callConfig = $this->createClientCredentialConfig(configuration: $configuration);
				break;
			case 'password':
				$callConfig = $this->createPasswordConfig(configuration: $configuration);
				break;
			default:
				throw new BadRequestException(message: 'Grant type not supported');

		}
		//@todo: custom config

		$client = new Client();

		$response = $client->post(uri: $endpoint, options: $callConfig);

		$result = json_decode(json: $response->getBody()->getContents(), associative: true);

		if(isset($configuration['tokenLocation']) === true) {
			return $result[$configuration['tokenLocation']];
		}

		return $result['access_token'];
	}
	private function getRSJWK(array $configuration): ?JWK
	{
		$stamp = microtime().getmypid();
		file_put_contents("/srv/api/var/privatekey-$stamp", $configuration['secret']);
		$jwk = null;
		$filename = "/srv/api/var/privatekey-$stamp";
		try{
			$jwk = JWKFactory::createFromKeyFile(
				$filename,
				null,
				['use' => 'sig']
			);
		}
		catch(\Exception $exception) {

		}

		unlink($filename);

		return $jwk;
	}

	private function getHSJWK(array $configuration): ?JWK
	{
		return new JWK(
			[
				'kty' => 'oct',
				'k'   => rtrim(string: base64_encode(addslashes($configuration['secret'])), characters: '='),
			]
		);
	}

	private function getJWTPayload(array $configuration): array
	{
		$renderedPayload = $this->twig->createTemplate($configuration['payload'])->render($configuration);

		return json_decode($renderedPayload, true);
	}

	private function getJWK(array $configuration): ?JWK
	{
		$jwk = null;
		if (in_array(needle: $configuration['algorithm'], haystack: ['HS256', 'HS512'])) {
			$jwk = $this->getHSJWK($configuration);
		} else if (in_array(needle: $configuration['algorithm'], haystack: ['RS256', 'RS384', 'RS512'])) {
			$jwk = $this->getRSJWK($configuration);
		}

		return $jwk;
	}

	private function generateJWT(array $payload, JWK $jwk, string $algorithm): string
	{
		$algorithmManager = new AlgorithmManager([
			new HS256(),
			new HS384(),
			new HS512(),
			new RS256(),
			new RS384(),
			new RS512()
		]);
		$jwsBuilder 	  = new JWSBuilder($algorithmManager);
		$jwsSerializer	  = new CompactSerializer();


		try {
			$jws = $jwsBuilder
				->create()
				->withPayload(json_encode($payload))
				->addSignature($jwk, ['alg' => $algorithm])
				->build();
		} catch (\Exception $e) {
			return $e->getMessage();
		}

		return $jwsSerializer->serialize($jws, 0);
	}

	public function fetchJWTToken (array $configuration): string
	{
		$diff = array_diff(self::REQUIRED_PARAMETERS_JWT, array_keys(array: $configuration));
		if ($diff !== []) {
			throw new BadRequestException(message: 'Some required parameters are not set: [' . implode(separator: ',', array: $diff) . ']');
		}

		$payload = $this->getJWTPayload($configuration);
		$jwk 	 = $this->getJWK($configuration);

		if($jwk === null) {
			throw new BadRequestException('No JWK key could be formed with given data');
		}

		return $this->generateJWT($payload, $jwk, $configuration['algorithm']);
	}

}
