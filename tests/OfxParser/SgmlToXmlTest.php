<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Tests;

use Beccha\OfxParser\Exception\FileNotFoundException;
use Beccha\OfxParser\Exception\OfxTagNotFoundException;
use Beccha\OfxParser\Exception\XmlContentNotFoundException;
use Beccha\OfxParser\Service\SgmlToXml;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class SgmlToXmlTest extends TestCase
{
    private SimpleXMLElement $data;

    protected function setUp(): void
    {
        $sgmlToXml = new SgmlToXml();
        $this->data = $sgmlToXml->parse(__DIR__ . '/../fixtures/SgmlToXml.xml');
    }

    public function testSgmlHeaderIsRemoved(): void
    {
        $this->assertNotFalse($this->data);
        $this->assertInstanceOf(SimpleXMLElement::class, $this->data);
    }

    public function testIGetAnExceptionWhenNoOfxTagIsFoundWithinTheFile(): void
    {
        $sgmlToXml = new SgmlToXml();
        $this->expectException(OfxTagNotFoundException::class);
        $this->data = $sgmlToXml->parse(__DIR__ . '/../fixtures/not_ofx.xml');
    }

    public function testIGetAnExceptionWhenNoFileIsFound(): void
    {
        $sgmlToXml = new SgmlToXml();
        $this->expectException(FileNotFoundException::class);
        $this->data = $sgmlToXml->parse(__DIR__ . '/../fixtures/not_existing.xml');
    }

    public function testIGetAnExceptionWhenNoXmlContentIsFound(): void
    {
        $sgmlToXml = new SgmlToXml();
        $this->expectException(XmlContentNotFoundException::class);
        $this->data = $sgmlToXml->parse(__DIR__ . '/../fixtures/not_xml.xml');
    }
}
