<?php

namespace OCA\OpenConnector\Twig;

use OCA\OpenConnector\Db\SourceMapper;
use OCA\OpenConnector\Service\AuthenticationService;
use Twig\Extension\RuntimeExtensionInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class AuthenticationRuntimeLoader implements RuntimeLoaderInterface
{
	public function __construct(
		private readonly AuthenticationService $authenticationService,
	)
	{
	}

	public function load(string $class)
	{

		if ($class === AuthenticationRuntime::class) {
			return new AuthenticationRuntime($this->authenticationService);
		}
		return null;
	}
}
