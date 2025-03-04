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

	private function arrayToXml(array $data, SimpleXMLElement $xml = null, string $rootTag = 'response'): string
	{
		// Determine the root tag from $data or default to 'response'
		if ($xml === null) {
			$rootTag = $data['@root'] ?? 'response';
			unset($data['@root']); // Ensure @root does not appear in the XML itself
			$xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><$rootTag/>", LIBXML_NOEMPTYTAG);
		}

		foreach ($data as $key => $value) {
			// Correctly handle @attributes
			if ($key === '@attributes' && is_array($value)) {
				foreach ($value as $attrKey => $attrValue) {
					$xml->addAttribute($attrKey, htmlspecialchars($attrValue, ENT_XML1, 'UTF-8'));
				}
				continue;
			}

			// Ensure XML-safe tag names
			$key = ltrim($key, '@'); // Remove @, except for @attributes (which is already processed)
			$key = is_numeric($key) ? "item$key" : $key;

			if (is_array($value)) {
				$subnode = $xml->addChild($key);
				$this->arrayToXml($value, $subnode);
			} else {
				$xml->addChild($key, htmlspecialchars($value, ENT_XML1, 'UTF-8'));
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
