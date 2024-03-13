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
     */
    public function testICanGenerateAnOfxFile(): void
    {
        $ofxFile = __DIR__ . '/fixtures/verified/official.ofx.xml';
        $ofsContent = (new Parser())->loadFromFile($ofxFile);

        $signOn = $ofsContent->getSignOn();
        $bank = $ofsContent->getBankAccounts();
        $toOfx = new ToOfx($signOn, $bank);

        $this->assertStringEqualsFile(
            $ofxFile,
            $toOfx->generate()
        );
    }
}
