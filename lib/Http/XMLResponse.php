<?php

namespace OCA\OpenConnector\Http;

use OCP\AppFramework\Http\Response;
use SimpleXMLElement;
use DOMDocument;

class XMLResponse extends Response
{
	private string $xmlData;

	public function __construct(array $data, int $status = 200)
	{
		parent::__construct();
		$this->addHeader('Content-Type', 'application/xml; charset=UTF-8');
		$this->setStatus($status);
		$this->xmlData = $this->arrayToXml($data);
	}

	public function render(): string
	{
		return $this->xmlData;
	}

	private function arrayToXml(array $data, SimpleXMLElement $xml = null): string
	{
		if ($xml === null) {
			$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><response/>', LIBXML_NOEMPTYTAG);
		}

		foreach ($data as $key => $value) {
			// Ensure XML-safe tag names
			$key = str_replace('@', '', $key);

			if (is_array($value)) {
				$subnode = $xml->addChild(is_numeric($key) ? "item$key" : $key);
				$this->arrayToXml($value, $subnode);
			} else {
				$xml->addChild(is_numeric($key) ? "item$key" : $key, htmlspecialchars($value, ENT_XML1, 'UTF-8'));
			}
		}

		// Convert SimpleXMLElement to formatted XML with declaration
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = false; // Prevent unnecessary spaces
		$dom->loadXML($xml->asXML());

		return $dom->saveXML(); // Ensures the correct declaration is always included
	}
}
