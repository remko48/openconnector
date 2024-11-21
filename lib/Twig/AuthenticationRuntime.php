<?php

namespace OCA\OpenConnector\Twig;

use Adbar\Dot;
use GuzzleHttp\Exception\GuzzleException;
use OCA\OpenConnector\Db\Source;
use OCA\OpenConnector\Service\AuthenticationService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
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
		$configuration = new Dot($source->getConfiguration(), true);

		$authConfig = $configuration->get('authentication');

		return $this->authService->fetchOAuthTokens(
			configuration: $authConfig
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
		$configuration = new Dot($source->getConfiguration(), true);

		$authConfig = $configuration->get('authentication');

		return $this->authService->fetchJWTToken(
			configuration: $authConfig
		);
	}
}
