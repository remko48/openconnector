<?php

namespace OCA\OpenConnector\Twig;

use GuzzleHttp\Exception\GuzzleException;
use OCA\OpenConnector\Db\Source;
use OCA\OpenConnector\Service\AuthenticationService;
use Twig\Extension\RuntimeExtensionInterface;

class AuthenticationRuntime implements RuntimeExtensionInterface
{
	public function __construct(
		private readonly AuthenticationService $authService,
	) {

	}


	/**
	 * Checks if the given array has at least the required keys to continue checkin oauth or a jwt token.
	 *
	 * The Required keys:
	 * 'authentication.algorithm',
	 * 'authentication.secret',
	 * 'authentication.payload'
	 *
	 * @param array $arrayCheck The PHP array to check if all required keys are present.
	 * @return bool True if all required keys are present, false if not.
	 */
	private function checkRequiredKeys(array $arrayCheck): bool
	{
		$requiredKeys = [
			'authentication.algorithm',
			'authentication.secret',
			'authentication.payload'
		];

		foreach ($requiredKeys as $key) {
			if (array_key_exists($key, $arrayCheck) === false) {
				echo "Key '$key' is missing.\n";
				return false; // Stop further execution if any key is missing
			}
		}

		return true;
	}

	/**
	 * Add an oauth token to the configuration.
	 *
	 * @param Source $source
	 * @return string
	 *
	 * @throws GuzzleException
	 */
	public function oauthToken(Source $source): string
	{
		$configuration = $source->getConfiguration();

		if ($this->checkRequiredKeys(arrayCheck: $configuration) === false) {
			// We should do something here
		}

		$configuration['algorithm'] = $configuration['authentication.algorithm'];
		$configuration['secret'] = $configuration['authentication.secret'];
		$configuration['payload'] = json_decode($configuration['authentication.payload'], true);

		return $this->authService->fetchOAuthTokens(
			configuration: $configuration
		);
	}

	/**
	 * Add a jwt token to the configuration.
	 *
	 * @param Source $source The source to run.
	 * @return string
	 * @throws GuzzleException
	 */
	public function jwtToken(Source $source): string
	{
		$configuration = $source->getConfiguration();

		if ($this->checkRequiredKeys(arrayCheck: $configuration) === false) {
			// We should do something here
		}

		$configuration['algorithm'] = $configuration['authentication.algorithm'];
		$configuration['secret'] = $configuration['authentication.secret'];
		$configuration['payload'] = json_decode($configuration['authentication.payload'], true);

		return $this->authService->fetchJWTToken(
			configuration: $configuration
		);
	}
}
