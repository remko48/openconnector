<?php

namespace OCA\OpenConnector\Http;

use OCP\AppFramework\Http\Response;
use SimpleXMLElement;

class XMLResponse extends Response
{
	public function __construct(array $data, int $status = 200)
	{
		parent::__construct();
		$this->addHeader('Content-Type', 'application/xml');
		$this->setStatus($status);
		$this->xmlData = $this->arrayToXml($data);
	}

	public function render()
	{
		return $this->xmlData;
	}

	private function arrayToXml(array $data, SimpleXMLElement $xml = null): string
	{
		if ($xml === null) {
			$xml = new SimpleXMLElement('<response/>');
		}

		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$subnode = $xml->addChild(is_numeric($key) ? "item$key" : $key);
				$this->arrayToXml($value, $subnode);
			} else {
				$xml->addChild(is_numeric($key) ? "item$key" : $key, htmlspecialchars($value));
			}
		}

		return $xml->asXML();
	}
}
