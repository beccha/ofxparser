<?php

declare(strict_types=1);

namespace OfxParser;

use OfxParser\Service\SgmlToXml;
use PHPUnit\Framework\TestCase;

class SgmlToXmlTest extends TestCase
{
    protected function setUp(): void
    {
        $sgmlToXml = new SgmlToXml();
        $this->data = $sgmlToXml->parse(__DIR__ . '/../fixtures/SgmlToXml.xml');
    }

    public function testSgmlHeaderIsRemoved(): void
    {
        $this->assertNotFalse($this->data);
        $this->assertInstanceOf(\SimpleXMLElement::class, $this->data);
    }
}
