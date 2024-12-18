<?php

namespace OCA\OpenConnector\Exception;

use Exception;

class AuthenticationException extends Exception
{
	private array $details;
	public function __construct(string $message, array $details) {
		$this->details = $details;
		parent::__construct($message);
	}

	public function getDetails(): array
	{
		return $this->details;
	}
}
