<?php

namespace OCA\OpenConnector\Twig;

use OCA\OpenConnector\Db\Mapping;
use OCA\OpenConnector\Db\MappingMapper;
use OCA\OpenConnector\Db\Source;
use OCA\OpenConnector\Service\AuthenticationService;
use OCA\OpenConnector\Service\MappingService;
use Twig\Extension\RuntimeExtensionInterface;

class MappingRuntime implements RuntimeExtensionInterface
{
	public function __construct(
		private readonly MappingService $mappingService,
		private readonly MappingMapper  $mappingMapper
	) {

	}

	public function executeMapping(Mapping|array|string|int $mapping, array $input, bool $list = false): array
	{
		if(is_array($mapping) === true) {
			$mappingObject = new Mapping();
			$mappingObject->hydrate($mapping);

			$mapping = $mappingObject;
		} else if (is_string($mapping) === true || is_int($mapping) === true) {
			$mapping = $this->mappingMapper->find($mapping);
		}


		return $this->mappingService->executeMapping(
			mapping: $mapping, input: $input, list: $list
		);
	}


}