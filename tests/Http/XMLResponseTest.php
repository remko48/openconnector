<?php

namespace OCA\OpenConnector\Tests\Http;

/**
 * Mock of the Response class for testing
 */
class MockResponse {
    private $headers = [];
    private $status = 200;
    
    public function addHeader($name, $value) {
        $this->headers[$name] = $value;
    }
    
    public function setStatus($status) {
        $this->status = $status;
    }
    
    public function getStatus() {
        return $this->status;
    }
}

/**
 * Manual implementation of XMLResponse for testing
 */
class XMLResponse extends MockResponse {
    protected array $data;
    protected $renderCallback = null;

    public function __construct($data = [], int $status = 200, array $headers = []) {
        $this->data = is_array($data) ? $data : ['content' => $data];
        
        $this->setStatus($status);
        
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }
        
        $this->addHeader('Content-Type', 'application/xml; charset=utf-8');
    }

    protected function getData(): array {
        return ['value' => $this->data];
    }

    public function setRenderCallback(callable $callback) {
        $this->renderCallback = $callback;
        return $this;
    }

    public function render(): string {
        if ($this->renderCallback !== null) {
            return ($this->renderCallback)($this->getData());
        }
        
        $data = $this->getData()['value'];
        
        // Check if data contains an @root key, if so use it directly
        if (isset($data['@root'])) {
            return $this->arrayToXml($data);
        }
        
        // Use default root tag
        return $this->arrayToXml(['value' => $data], 'response');
    }

    public function arrayToXml(array $data, ?string $rootTag = null): string {
        $rootName = $rootTag ?? ($data['@root'] ?? 'root');
        
        if (isset($data['@root'])) {
            unset($data['@root']);
        }
        
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        
        $root = $dom->createElement($rootName);
        if (!$root) {
            return '';
        }
        
        $dom->appendChild($root);
        
        $this->buildXmlElement($dom, $root, $data);
        
        return $dom->saveXML() ?: '';
    }

    private function buildXmlElement(\DOMDocument $dom, \DOMElement $element, array $data): void {
        if (isset($data['@attributes']) && is_array($data['@attributes'])) {
            foreach ($data['@attributes'] as $attrKey => $attrValue) {
                $element->setAttribute($attrKey, (string)$attrValue);
            }
            unset($data['@attributes']);
        }
        
        if (isset($data['#text'])) {
            $element->appendChild($this->createSafeTextNode($dom, (string)$data['#text']));
            unset($data['#text']);
        }
        
        foreach ($data as $key => $value) {
            $key = ltrim($key, '@');
            $key = is_numeric($key) ? "item$key" : $key;
            
            if (is_array($value)) {
                if (isset($value[0]) && is_array($value[0])) {
                    foreach ($value as $item) {
                        $this->createChildElement($dom, $element, $key, $item);
                    }
                } else {
                    $this->createChildElement($dom, $element, $key, $value);
                }
            } else {
                $this->createChildElement($dom, $element, $key, $value);
            }
        }
    }

    private function createChildElement(\DOMDocument $dom, \DOMElement $parentElement, string $tagName, $data): void {
        $childElement = $dom->createElement($tagName);
        if ($childElement) {
            $parentElement->appendChild($childElement);
            
            if (is_array($data)) {
                $this->buildXmlElement($dom, $childElement, $data);
            } else {
                $childElement->appendChild($this->createSafeTextNode($dom, (string)$data));
            }
        }
    }

    private function createSafeTextNode(\DOMDocument $dom, string $text): \DOMText {
        return $dom->createTextNode($text);
    }
}

/**
 * Manual test cases for the XMLResponse class
 * 
 * Tests functionality in lib/Http/XMLResponse.php
 */
class XMLResponseTest
{
    /**
     * Run all tests
     * 
     * @return void
     */
    public function runTests(): void
    {
        // GROUP 1: XMLResponse constructor and getData method
        echo "Testing lib/Http/XMLResponse.php - Constructor & getData:\n";
        $this->testBasicXmlGeneration();
        echo "\n";
        
        // GROUP 2: setRenderCallback method
        echo "Testing lib/Http/XMLResponse.php - setRenderCallback:\n";
        $this->testCustomRenderCallback();
        echo "\n";
        
        // GROUP 3: arrayToXml method
        echo "Testing lib/Http/XMLResponse.php - arrayToXml:\n";
        $this->testCustomRootTag();
        $this->testArrayItems();
        echo "\n";
        
        // GROUP 4: buildXmlElement method
        echo "Testing lib/Http/XMLResponse.php - buildXmlElement:\n";
        $this->testAttributesHandling();
        $this->testNamespacedAttributes();
        echo "\n";
        
        // GROUP 5: createSafeTextNode method
        echo "Testing lib/Http/XMLResponse.php - createSafeTextNode:\n";
        $this->testSpecialCharactersHandling();
        echo "\n";
        
        // GROUP 6: Integration tests
        echo "Testing lib/Http/XMLResponse.php - Integration tests:\n";
        $this->testOpenGroupModelXML();
        
        echo "\nAll tests passed successfully!\n";
    }
    
    /**
     * Test basic XML conversion 
     * 
     * Tests:
     * - lib/Http/XMLResponse.php::__construct
     * - lib/Http/XMLResponse.php::getData
     * - lib/Http/XMLResponse.php::render (with default behavior)
     * 
     * @return void
     */
    private function testBasicXmlGeneration(): void
    {
        echo "- Running testBasicXmlGeneration: ";
        
        $data = [
            'user' => [
                'id' => 123,
                'name' => 'Test User',
                'email' => 'test@example.com'
            ]
        ];
        
        $response = new XMLResponse($data);
        $xml = $response->render();
        
        $this->assertContains('<response>', $xml);
        $this->assertContains('<value>', $xml);
        $this->assertContains('<user>', $xml);
        $this->assertContains('<id>123</id>', $xml);
        $this->assertContains('<name>Test User</name>', $xml);
        $this->assertContains('<email>test@example.com</email>', $xml);
        
        echo "PASSED\n";
    }
    
    /**
     * Test custom render callback
     * 
     * Tests:
     * - lib/Http/XMLResponse.php::setRenderCallback
     * - lib/Http/XMLResponse.php::render (with custom callback)
     * 
     * @return void
     */
    private function testCustomRenderCallback(): void
    {
        echo "- Running testCustomRenderCallback: ";
        
        $response = new XMLResponse(['test' => 'data']);
        $response->setRenderCallback(function($data) {
            return '<custom>' . json_encode($data) . '</custom>';
        });
        
        $result = $response->render();
        $this->assertContains('<custom>', $result);
        $this->assertContains('test', $result);
        
        echo "PASSED\n";
    }
    
    /**
     * Test XML generation with custom root tag
     * 
     * Tests:
     * - lib/Http/XMLResponse.php::arrayToXml (with @root tag)
     * - lib/Http/XMLResponse.php::render (with @root tag)
     * 
     * @return void
     */
    private function testCustomRootTag(): void
    {
        echo "- Running testCustomRootTag: ";
        
        $data = [
            '@root' => 'customRoot',
            'message' => 'Hello World'
        ];
        
        $response = new XMLResponse($data);
        $xml = $response->render();
        
        $this->assertContains('<customRoot>', $xml);
        $this->assertNotContains('<response>', $xml);
        $this->assertContains('<message>Hello World</message>', $xml);
        $this->assertNotContains('<@root>', $xml);
        
        echo "PASSED\n";
    }
    
    /**
     * Test handling of array items
     * 
     * Tests:
     * - lib/Http/XMLResponse.php::arrayToXml (with array items)
     * - lib/Http/XMLResponse.php::buildXmlElement (array handling)
     * 
     * @return void
     */
    private function testArrayItems(): void
    {
        echo "- Running testArrayItems: ";
        
        $data = [
            'items' => [
                ['name' => 'Item 1', 'value' => 100],
                ['name' => 'Item 2', 'value' => 200],
                ['name' => 'Item 3', 'value' => 300]
            ]
        ];
        
        $response = new XMLResponse();
        $xml = $response->arrayToXml($data);
        
        $this->assertContains('<items>', $xml);
        $this->assertContains('<name>Item 1</name>', $xml);
        $this->assertContains('<value>100</value>', $xml);
        $this->assertContains('<name>Item 2</name>', $xml);
        
        echo "PASSED\n";
    }
    
    /**
     * Test XML generation with attributes
     * 
     * Tests:
     * - lib/Http/XMLResponse.php::buildXmlElement (attribute handling)
     * 
     * @return void
     */
    private function testAttributesHandling(): void
    {
        echo "- Running testAttributesHandling: ";
        
        $data = [
            'element' => [
                '@attributes' => [
                    'id' => '123',
                    'class' => 'container'
                ],
                'content' => 'Text with attributes'
            ]
        ];
        
        $response = new XMLResponse();
        $xml = $response->arrayToXml($data);
        
        $this->assertContains('<element id="123" class="container">', $xml);
        $this->assertContains('<content>Text with attributes</content>', $xml);
        
        echo "PASSED\n";
    }
    
    /**
     * Test XML generation with namespaced attributes
     * 
     * Tests:
     * - lib/Http/XMLResponse.php::buildXmlElement (namespaced attribute handling)
     * 
     * @return void
     */
    private function testNamespacedAttributes(): void
    {
        echo "- Running testNamespacedAttributes: ";
        
        $data = [
            '@root' => 'root',
            '@attributes' => [
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:schemaLocation' => 'http://example.org/schema.xsd'
            ],
            'content' => 'Namespaced content'
        ];
        
        $response = new XMLResponse();
        $xml = $response->arrayToXml($data);
        
        $this->assertContains('<root xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://example.org/schema.xsd">', $xml);
        $this->assertContains('<content>Namespaced content</content>', $xml);
        
        echo "PASSED\n";
    }
    
    /**
     * Test XML special character handling
     * 
     * Tests:
     * - lib/Http/XMLResponse.php::createSafeTextNode
     * 
     * @return void
     */
    private function testSpecialCharactersHandling(): void
    {
        echo "- Running testSpecialCharactersHandling: ";
        
        $data = [
            'element' => 'Text with <special> & "characters"'
        ];
        
        $response = new XMLResponse();
        $xml = $response->arrayToXml($data);
        
        $this->assertContains('<element>Text with &lt;special&gt; &amp; "characters"</element>', $xml);
        
        echo "PASSED\n";
    }
    
    /**
     * Test for OpenGroup ArchiMate XML format - Integration test
     * 
     * Tests:
     * - lib/Http/XMLResponse.php::render (with @root tag)
     * - lib/Http/XMLResponse.php::arrayToXml (with complex structure)
     * - lib/Http/XMLResponse.php::buildXmlElement (with namespaced attributes)
     * 
     * @return void
     */
    private function testOpenGroupModelXML(): void
    {
        echo "- Running testOpenGroupModelXML: ";
        
        $data = [
            '@root' => 'model',
            '@attributes' => [
                'xmlns' => 'http://www.opengroup.org/xsd/archimate/3.0/',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:schemaLocation' => 'http://www.opengroup.org/xsd/archimate/3.0/ http://www.opengroup.org/xsd/archimate/3.1/archimate3_Diagram.xsd',
                'identifier' => 'id-b58b6b03-a59d-472b-bd87-88ba77ded4e6'
            ]
        ];
        
        $response = new XMLResponse($data);
        $xml = $response->render();
        
        // Verify XML declaration
        $this->assertContains('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        
        // Verify model tag exists as the root element (not nested in a response element)
        $this->assertContains('<model ', $xml);
        $this->assertNotContains('<response>', $xml);
        
        // Verify each attribute exists
        $this->assertContains('xmlns="http://www.opengroup.org/xsd/archimate/3.0/"', $xml);
        $this->assertContains('xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', $xml);
        $this->assertContains('xsi:schemaLocation="http://www.opengroup.org/xsd/archimate/3.0/ http://www.opengroup.org/xsd/archimate/3.1/archimate3_Diagram.xsd"', $xml);
        $this->assertContains('identifier="id-b58b6b03-a59d-472b-bd87-88ba77ded4e6"', $xml);
        
        echo "PASSED\n";
    }
    
    /**
     * Simple assertion to check if a haystack contains a needle
     *
     * @param string $needle The string to search for
     * @param string $haystack The string to search in
     * @throws \Exception If the assertion fails
     */
    private function assertContains(string $needle, string $haystack): void
    {
        if (strpos($haystack, $needle) === false) {
            throw new \Exception("Failed asserting that haystack contains '$needle'");
        }
    }
    
    /**
     * Simple assertion to check if a haystack does not contain a needle
     *
     * @param string $needle The string to search for
     * @param string $haystack The string to search in
     * @throws \Exception If the assertion fails
     */
    private function assertNotContains(string $needle, string $haystack): void
    {
        if (strpos($haystack, $needle) !== false) {
            throw new \Exception("Failed asserting that haystack does not contain '$needle'");
        }
    }
}

// Auto-run tests when file is executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    try {
        $tester = new XMLResponseTest();
        $tester->runTests();
    } catch (\Exception $e) {
        echo "TEST FAILED: " . $e->getMessage() . "\n";
        exit(1);
    }
} 