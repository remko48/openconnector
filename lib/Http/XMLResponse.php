<?php

namespace OCA\OpenConnector\Http;

use OCP\AppFramework\Http\Response;
use SimpleXMLElement;
use DOMDocument;

/**
 * Class XMLResponse
 * Handles conversion of array data to XML response
 */
class XMLResponse extends Response
{
	/**
	 * The XML data as a string
	 */
	private string $xmlData;

	/**
	 * Constructor
	 *
	 * @param array $data The data to convert to XML
	 * @param int $status HTTP status code
	 */
	public function __construct(array $data, int $status = 200)
	{
		parent::__construct();
		$this->addHeader('Content-Type', 'application/xml; charset=UTF-8');
		$this->setStatus($status);
		$this->xmlData = $this->arrayToXml($data);
	}

	/**
	 * Render the XML response
	 *
	 * @return string The XML string
	 */
	public function render(): string
	{
		return $this->xmlData;
	}

	/**
	 * Convert an array to XML
	 *
	 * @param array $data The array to convert
	 * @param SimpleXMLElement|null $xml The XML element to add to
	 * @param string $rootTag The root tag name
	 * @return string The XML string
	 */
	private function arrayToXml(array $data, ?SimpleXMLElement $xml = null, string $rootTag = 'response'): string
	{
		// Determine the root tag from $data or default to 'response'
		if ($xml === null) {
			$rootTag = $data['@root'] ?? 'response';
			unset($data['@root']); // Ensure @root does not appear in the XML itself
			$xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><$rootTag/>");
		}

		foreach ($data as $key => $value) {
			// Handle @attributes
			if ($key === '@attributes' && is_array($value)) {
				foreach ($value as $attrKey => $attrValue) {
					// Special handling for attributes containing ':'
					if (str_contains($attrKey, ':')) {
						$xml->addAttribute($attrKey, htmlspecialchars((string)$attrValue, ENT_XML1, 'UTF-8'), 'http://www.w3.org/XML/1998/namespace');
					} else {
						$xml->addAttribute($attrKey, htmlspecialchars((string)$attrValue, ENT_XML1, 'UTF-8'));
					}
				}
				continue;
			}

			// Handle text content
			if ($key === '#text') {
				$xml[0] = htmlspecialchars((string)$value, ENT_XML1, 'UTF-8');
				continue;
			}

			// Ensure XML-safe tag names
			$key = ltrim($key, '@'); // Remove @, except for @attributes (which is already processed)
			$key = is_numeric($key) ? "item$key" : $key;

			if (is_array($value)) {
				// Handle arrays that should be repeated elements
				if (isset($value[0]) && is_array($value[0])) {
					foreach ($value as $item) {
						$subnode = $xml->addChild($key);
						$this->arrayToXml($item, $subnode);
					}
				} else {
					$subnode = $xml->addChild($key);
					$this->arrayToXml($value, $subnode);
				}
			} else {
				$xml->addChild($key, htmlspecialchars((string)$value, ENT_XML1, 'UTF-8'));
			}
		}

		// Convert SimpleXMLElement to formatted XML with declaration
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true; // Enable formatting for readability
		$dom->loadXML($xml->asXML());

		return $dom->saveXML();
	}
}
