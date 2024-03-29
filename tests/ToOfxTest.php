<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Tests;

use Beccha\OfxParser\Ofx;
use Beccha\OfxParser\Parser;
use Beccha\OfxParser\ToOfx;
use Exception;
use PHPUnit\Framework\TestCase;

class ToOfxTest extends TestCase
{
    /**
     * @throws Exception
     *
     */
    public function testICanGenerateAnOfxFile(): void
    {
        $ofxFile = __DIR__ . '/fixtures/verified/official.ofx.xml';
        $ofxContent = (new Parser())->loadFromFile($ofxFile);

        // Data as object
        $signOn = $ofxContent->getSignOn();
        $banks = $ofxContent->getBankAccounts();

        // Object to xml
        $toOfx = new ToOfx($signOn, $banks);

        // Check that original data is the same as the converted one
        if ($xml = simplexml_load_string($toOfx->generate())) {
            $newOfxContent = new Ofx($xml);
            $this->assertEquals($ofxContent, $newOfxContent);
        }
    }
}
