<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Tests;

use Beccha\OfxParser\Entity\BankAccount;
use Beccha\OfxParser\Entity\Institution;
use Beccha\OfxParser\Entity\Payee;
use Beccha\OfxParser\Entity\SignOn;
use Beccha\OfxParser\Entity\Statement;
use Beccha\OfxParser\Entity\Status;
use Beccha\OfxParser\Entity\Transaction;
use Beccha\OfxParser\Ofx;
use Beccha\OfxParser\Parser;
use Beccha\OfxParser\ToOfx;
use Exception;
use PHPUnit\Framework\TestCase;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ToOfxTest extends TestCase
{
    /**
     * @throws Exception
     *
     */
    public function testICanGenerateAnOfxFile(): void
    {
        // FIXME
        $this->markTestSkipped('Check that this test works before moving on.');

        $ofxFile = __DIR__ . '/fixtures/verified/official.ofx.xml';
        $ofxContent = (new Parser())->loadFromFile($ofxFile);

        // Data as object
        $signOn = $ofxContent->getSignOn();
        $banks = $ofxContent->getBankAccounts();

        // Data as xml
        $toOfx = new ToOfx($signOn, $banks);

        // Back to object
        $newOfxContent = new Ofx(simplexml_load_string($toOfx->generate()));

        $this->assertEquals($ofxContent, $newOfxContent);
    }
}
