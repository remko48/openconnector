<?php

/**
 * Handler for XML sources.
 *
 * @category  Service
 * @package   OpenConnector
 * @author    Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (C) 2024 Conduction B.V. All rights reserved.
 * @license   EUPL 1.2
 * @version   GIT: <git_id>
 * @link      https://openregister.app
 *
 * @since 1.0.0 - Description of when this class was added
 */

namespace OCA\OpenConnector\Service\SourceHandler;

use SimpleXMLElement;

/**
 * Handler for XML sources.
 *
 * @category  Service
 * @package   OpenConnector
 * @author    Conduction B.V. <info@conduction.nl>
 * @copyright Copyright (C) 2024 Conduction B.V. All rights reserved.
 * @license   EUPL 1.2
 * @version   GIT: <git_id>
 * @link      https://openregister.app
 *
 * @since 1.0.0 - Description of when this class was added
 */
class XmlHandler extends AbstractSourceHandler
{


    /**
     * Checks if this handler can handle the given source type.
     *
     * @param string $sourceType The type of source to check
     *
     * @return bool True if this handler can handle the source type
     *
     * @psalm-pure
     * @phpstan-return bool
     */
    public function canHandle(string $sourceType): bool
    {
        return $sourceType === 'xml';

    }//end canHandle()


    /**
     * Gets all objects from an XML source.
     *
     * @param Source $source      The source to fetch from
     * @param array  $config      Configuration for the source
     * @param bool   $isTest      Whether this is a test run
     * @param int    $currentPage Current page for pagination
     * @param array  $headers     Optional headers for the request
     * @param array  $query       Optional query parameters
     *
     * @return array Array of objects fetched from the source
     *
     * @throws \Exception If there is an error fetching the objects
     */
    public function getAllObjects(
        Source $source,
        array $config,
        bool $isTest=false,
        int $currentPage=1,
        array $headers=[],
        array $query=[]
    ): array {
        $this->checkRateLimit($source);

        $endpoint      = ($config['endpoint'] ?? '');
        $requestConfig = [
            'headers' => $headers,
            'query'   => $query,
        ];

        $response = $this->callService->call(
            source: $source,
            endpoint: $endpoint,
            config: $requestConfig
        )->getResponse();

        if ($response === null) {
            return [];
        }

        $xml = $this->parseXmlResponse($response['body']);
        if ($xml === false) {
            return [];
        }

        $result  = $this->xmlToArray($xml);
        $objects = $this->extractObjects($result, $config);

        if ($isTest === true && empty($objects) === false) {
            return [$objects[0]];
        }

        return $objects;

    }//end getAllObjects()


    /**
     * Fetches a single object from the source.
     *
     * @param Source $source   The source to fetch from
     * @param string $endpoint The endpoint to fetch from
     * @param array  $config   Configuration for the source
     * @param array  $headers  Optional headers for the request
     * @param array  $query    Optional query parameters
     *
     * @return array The fetched object
     *
     * @throws \Exception If there is an error fetching the object
     */
    public function getObject(
        Source $source,
        string $endpoint,
        array $config,
        array $headers=[],
        array $query=[]
    ): array {
        $this->checkRateLimit($source);

        if (str_starts_with($endpoint, $source->getLocation()) === true) {
            $endpoint = str_replace(search: $source->getLocation(), replace: '', subject: $endpoint);
        }

        $requestConfig = [
            'headers' => $headers,
            'query'   => $query,
        ];

        $response = $this->callService->call(
            source: $source,
            endpoint: $endpoint,
            config: $requestConfig,
            read: true
        )->getResponse();

        $xml = $this->parseXmlResponse($response['body']);
        if ($xml === false) {
            return [];
        }

        return $this->xmlToArray($xml);

    }//end getObject()


    /**
     * Parses an XML string into a SimpleXMLElement.
     *
     * @param string $xmlString The XML string to parse
     *
     * @return \SimpleXMLElement|false The parsed XML or false on failure
     */
    private function parseXmlResponse(string $xmlString): \SimpleXMLElement | false
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
        libxml_use_internal_errors(false);

        return $xml;

    }//end parseXmlResponse()


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

        // Handle attributes.
        $attributes = $xml->attributes();
        if (count($attributes) > 0) {
            $result['@attributes'] = [];
            foreach ($attributes as $attrName => $attrValue) {
                $result['@attributes'][(string) $attrName] = (string) $attrValue;
            }
        }

        // Handle namespaced attributes.
        $namespaces = $xml->getNamespaces(true);
        foreach ($namespaces as $prefix => $namespace) {
            $nsAttributes = $xml->attributes($namespace);
            if (count($nsAttributes) > 0) {
                if (isset($result['@attributes']) === false) {
                    $result['@attributes'] = [];
                }

                foreach ($nsAttributes as $attrName => $attrValue) {
                    $nsAttrName                         = $prefix ? "$prefix:$attrName" : $attrName;
                    $result['@attributes'][$nsAttrName] = (string) $attrValue;
                }
            }
        }

        // Handle child elements.
        foreach ($xml->children() as $childName => $child) {
            $childArray = $this->xmlToArray($child);

            if (isset($result[$childName]) === true) {
                if (is_array($result[$childName]) === false || isset($result[$childName][0]) === false) {
                    $result[$childName] = [$result[$childName]];
                }

                $result[$childName][] = $childArray;
            } else {
                $result[$childName] = $childArray;
            }
        }

        // Handle text content.
        $text = trim((string) $xml);
        if (count($result) === 0 && $text !== '') {
            return ['#text' => $text];
        } else if ($text !== '') {
            $result['#text'] = $text;
        }

        return $result;

    }//end xmlToArray()


}//end class
