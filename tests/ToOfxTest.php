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
use Beccha\OfxParser\ToOfx;
use PHPUnit\Framework\TestCase;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ToOfxTest extends TestCase
{
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function testICanGenerateAnOfxFile(): void
    {
        $signOn = new SignOn(
            new Status('0', 'INFO', 'Success'),
            new \DateTime('2015-12-09T02:15:29.000000+0000'),
            'POR',
            new Institution('001', 'Banco do Brasil S/A'),
        );

        $transaction = new Transaction(
            'DEP',
            new \DateTime('2023-12-04'),
            1234,
            '2222',
            'BARCLAYS',
            'Debit',
            '',
            '',
            new Payee(
                'Sherlock Holmes',
                'Baker Street',
                '',
                '',
                'London',
                'London',
                'NW1 6XE',
                'United Kingdom',
                '077123345678',
            ),
        );

        $statement = new Statement(
            'EUR',
            [],
            new \DateTime('2023-12-04'),
            new \DateTime('2023-12-04'),
        );

        $bank = new BankAccount(
            '123456',
            '123456',
            'CHECKING',
            12.20,
            new \DateTime('2023-12-04'),
            '1111',
            $statement,
            'xxx',
        );

        $toOfx = new ToOfx($signOn, $bank);

        $this->assertStringEqualsFile(
            __DIR__ . '/fixtures/toOfx.ofx', $toOfx->generate()
        );
    }
}
