<?php

namespace OCA\openconnector\lib\Twig;

use OCA\OpenConnector\Db\SourceMapper;
use OCA\OpenConnector\Service\AuthenticationService;
use Twig\Extension\RuntimeExtensionInterface;

class AuthenticationRuntime implements RuntimeExtensionInterface
{
	public function __construct(
		private readonly AuthenticationService $authService,
		private readonly SourceMapper $sourceMapper,
	) {

	}

	public function oauthToken(string $endpoint, string $sourceId): string
	{
		$source = $this->sourceMapper->find(id: (int) $sourceId);

		return $this->authService->fetchOAuthTokens(
			endpoint: $endpoint,
			configuration: $source->getAuthenticationConfig()
		);
	}
	public function jwtToken(string $endpoint, string $sourceId): string
	{
		$source = $this->sourceMapper->find(id: (int) $sourceId);

		return $this->authService->fetchJWTToken(
			configuration: $source->getAuthenticationConfig()
		);
	}
}
