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
	 * Add an oauth token to the configuration.
	 *
	 * @param Source $source
	 * @return string
	 *
	 * @throws GuzzleException
	 */
	public function oauthToken(Source $source): string
	{
		return $this->authService->fetchOAuthTokens(
			configuration: $source->getAuthenticationConfig()
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
		return $this->authService->fetchJWTToken(
			configuration: $source->getAuthenticationConfig()
		);
	}
}
