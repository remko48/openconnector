<?php

namespace OCA\OpenConnector\Twig;

use OCA\OpenConnector\Db\Source;
use OCA\OpenConnector\Service\AuthenticationService;
use Twig\Extension\RuntimeExtensionInterface;

class AuthenticationRuntime implements RuntimeExtensionInterface
{
	public function __construct(
		private readonly AuthenticationService $authService,
	) {

	}

	public function oauthToken(string $endpoint, Source $source): string
	{

		return $this->authService->fetchOAuthTokens(
			endpoint: $endpoint,
			configuration: $source->getAuthenticationConfig()
		);
	}
	public function jwtToken(Source $source): string
	{
		return $this->authService->fetchJWTToken(
			configuration: $source->getAuthenticationConfig()
		);
	}
}
