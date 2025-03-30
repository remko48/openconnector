/**
 * Handler for SOAP sources without requiring PHP SOAP extension.
 *
 * @package     OpenConnector
 * @category    Service
 * @author      Conduction B.V. <info@conduction.nl>
 * @copyright   Copyright (C) 2024 Conduction B.V. All rights reserved.
 * @license     EUPL 1.2
 * @version     1.0.0
 * @link        https://openregister.app
 *
 * @since       1.0.0 - Description of when this class was added
 */
class SoapHandler extends AbstractSourceHandler
{
    /**
     * @inheritDoc
     */
    public function canHandle(string $sourceType): bool
    {
        return $sourceType === 'soap';
    }

    /**
     * @inheritDoc
     */
    public function getAllObjects(
        Source $source,
        array $config,
        bool $isTest = false,
        int $currentPage = 1,
        array $headers = [],
        array $query = []
    ): array {
        $this->checkRateLimit($source);

        if (!isset($config['soapAction'], $config['operation'])) {
            throw new \InvalidArgumentException('SOAP action and operation must be specified in config');
        }

        $soapRequest = $this->buildSoapRequest(
            operation: $config['operation'],
            parameters: $query,
            namespace: $config['namespace'] ?? null,
            soapVersion: $config['soapVersion'] ?? '1.1'
        );

        $headers = array_merge($headers, [
            'Content-Type' => 'text/xml;charset=UTF-8',
            'SOAPAction' => $config['soapAction']
        ]);

        $requestConfig = [
            'headers' => $headers,
            'body' => $soapRequest
        ];

        $response = $this->callService->call(
            source: $source,
            endpoint: $config['endpoint'] ?? '',
            method: 'POST',
            config: $requestConfig
        )->getResponse();

        if ($response === null) {
            return [];
        }

        $result = $this->parseSoapResponse($response['body']);
        $objects = $this->extractObjects($result, $config);

        if ($isTest && !empty($objects)) {
            return [$objects[0]];
        }

        return $objects;
    }

    /**
     * @inheritDoc
     */
    public function getObject(
        Source $source,
        string $endpoint,
        array $config,
        array $headers = [],
        array $query = []
    ): array {
        $this->checkRateLimit($source);

        if (!isset($config['soapAction'], $config['operation'])) {
            throw new \InvalidArgumentException('SOAP action and operation must be specified in config');
        }

        $soapRequest = $this->buildSoapRequest(
            operation: $config['operation'],
            parameters: $query,
            namespace: $config['namespace'] ?? null,
            soapVersion: $config['soapVersion'] ?? '1.1'
        );

        $headers = array_merge($headers, [
            'Content-Type' => 'text/xml;charset=UTF-8',
            'SOAPAction' => $config['soapAction']
        ]);

        $requestConfig = [
            'headers' => $headers,
            'body' => $soapRequest
        ];

        $response = $this->callService->call(
            source: $source,
            endpoint: $endpoint,
            method: 'POST',
            config: $requestConfig,
            read: true
        )->getResponse();

        return $this->parseSoapResponse($response['body']);
    }

    /**
     * Builds a SOAP request XML.
     *
     * @param string $operation The SOAP operation to call
     * @param array $parameters The parameters for the operation
     * @param string|null $namespace Optional namespace
     * @param string $soapVersion SOAP version to use ('1.1' or '1.2')
     *
     * @return string The SOAP request XML
     */
    private function buildSoapRequest(
        string $operation,
        array $parameters,
        ?string $namespace = null,
        string $soapVersion = '1.1'
    ): string {
        $nsPrefix = $namespace ? 'ns1:' : '';
        $ns = $namespace ? " xmlns:ns1=\"$namespace\"" : '';
        $envelope = $soapVersion === '1.2' ? 
            'http://www.w3.org/2003/05/soap-envelope' : 
            'http://schemas.xmlsoap.org/soap/envelope/';

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<SOAP-ENV:Envelope xmlns:SOAP-ENV=\"$envelope\"$ns>\n";
        $xml .= "  <SOAP-ENV:Body>\n";
        $xml .= "    <{$nsPrefix}{$operation}>\n";

        // Add parameters
        foreach ($parameters as $key => $value) {
            $xml .= $this->parameterToXml($key, $value, '      ');
        }

        $xml .= "    </{$nsPrefix}{$operation}>\n";
        $xml .= "  </SOAP-ENV:Body>\n";
        $xml .= "</SOAP-ENV:Envelope>";

        return $xml;
    }

    /**
     * Converts a parameter to XML format.
     *
     * @param string $key The parameter name
     * @param mixed $value The parameter value
     * @param string $indent Indentation string
     *
     * @return string The XML representation of the parameter
     */
    private function parameterToXml(string $key, mixed $value, string $indent): string
    {
        if (is_array($value)) {
            $xml = "$indent<$key>\n";
            foreach ($value as $subKey => $subValue) {
                $xml .= $this->parameterToXml($subKey, $subValue, $indent . '  ');
            }
            $xml .= "$indent</$key>\n";
            return $xml;
        }

        return "$indent<$key>" . htmlspecialchars($value) . "</$key>\n";
    }

    /**
     * Parses a SOAP response XML.
     *
     * @param string $response The SOAP response XML
     *
     * @return array The parsed response
     */
    private function parseSoapResponse(string $response): array
    {
        $xml = $this->parseXmlResponse($response);
        if ($xml === false) {
            return [];
        }

        // Remove SOAP envelope and get to the actual response data
        $body = $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
        if (!$body) {
            $body = $xml->children('http://www.w3.org/2003/05/soap-envelope')->Body;
        }

        if (!$body) {
            return [];
        }

        // Convert the response to array
        return $this->xmlToArray($body->children()[0]);
    }

    /**
     * Parses an XML string into a SimpleXMLElement.
     *
     * @param string $xmlString The XML string to parse
     *
     * @return \SimpleXMLElement|false The parsed XML or false on failure
     */
    private function parseXmlResponse(string $xmlString): \SimpleXMLElement|false
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
        libxml_use_internal_errors(false);

        return $xml;
    }

    /**
     * Converts a SimpleXMLElement to an array while preserving namespaced attributes.
     *
     * @param \SimpleXMLElement $xml The XML element to convert
     *
     * @return array The array representation
     */
    private function xmlToArray(\SimpleXMLElement $xml): array
    {
        $result = [];

        // Handle attributes
        $attributes = $xml->attributes();
        if (count($attributes) > 0) {
            $result['@attributes'] = [];
            foreach ($attributes as $attrName => $attrValue) {
                $result['@attributes'][(string)$attrName] = (string)$attrValue;
            }
        }

        // Handle namespaced attributes
        $namespaces = $xml->getNamespaces(true);
        foreach ($namespaces as $prefix => $namespace) {
            $nsAttributes = $xml->attributes($namespace);
            if (count($nsAttributes) > 0) {
                if (!isset($result['@attributes'])) {
                    $result['@attributes'] = [];
                }

                foreach ($nsAttributes as $attrName => $attrValue) {
                    $nsAttrName = $prefix ? "$prefix:$attrName" : $attrName;
                    $result['@attributes'][$nsAttrName] = (string)$attrValue;
                }
            }
        }

        // Handle child elements
        foreach ($xml->children() as $childName => $child) {
            $childArray = $this->xmlToArray($child);

            if (isset($result[$childName])) {
                if (!is_array($result[$childName]) || !isset($result[$childName][0])) {
                    $result[$childName] = [$result[$childName]];
                }
                $result[$childName][] = $childArray;
            } else {
                $result[$childName] = $childArray;
            }
        }

        // Handle text content
        $text = trim((string)$xml);
        if (count($result) === 0 && $text !== '') {
            return ['#text' => $text];
        } elseif ($text !== '') {
            $result['#text'] = $text;
        }

        return $result;
    }
} 