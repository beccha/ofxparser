<?php

namespace OfxParser;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class OfxTest extends TestCase
{
    protected SimpleXMLElement $ofxData;
    private Ofx $ofsContent;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $ofxFile = dirname(__DIR__) . '/fixtures/ofxdata-xml.ofx';
        $this->ofsContent = (new Parser())->loadFromFile($ofxFile);
    }

    public function testBuildsSignOn(): void
    {
        self::assertEquals('', $this->ofsContent->signOn->status->message);
        self::assertEquals('0', $this->ofsContent->signOn->status->code);
        self::assertEquals('INFO', $this->ofsContent->signOn->status->getSeverity());
        self::assertEquals('Success', $this->ofsContent->signOn->status->codeDesc);

        self::assertInstanceOf('DateTime', $this->ofsContent->signOn->date);
        self::assertEquals('ENG', $this->ofsContent->signOn->language);
        self::assertEquals('MYBANK', $this->ofsContent->signOn->institute->name);
        self::assertEquals('01234', $this->ofsContent->signOn->institute->id);
    }

    /**
     * @throws Exception
     */
    public function testBuildsMultipleBankAccounts(): void
    {
        $multiOfxFile = dirname(__DIR__) . '/fixtures/ofx-multiple-accounts-xml.ofx';
        $multiOfxData = simplexml_load_string(file_get_contents($multiOfxFile));
        $ofx = new Ofx($multiOfxData);

        self::assertCount(3, $ofx->bankAccounts);
        self::assertEmpty($ofx->bankAccount);
    }

    public function testICanCheckDetailsOfTransactions(): void
    {
        $transactions = $this->ofsContent->getTransactions();

        $expectedTransactions = [
            [
                'type'        => 'CREDIT',
                'typeDesc'    => 'Generic credit',
                'amount'      => 20000,
                'uniqueId'    => '980315001',
                'name'        => 'DEPOSIT',
                'memo'        => 'automatic deposit',
                'sic'         => '',
                'checkNumber' => ''
            ],
            [
                'type'        => 'CREDIT',
                'typeDesc'    => 'Generic credit',
                'amount'      => 15000,
                'uniqueId'    => '980310001',
                'name'        => 'TRANSFER',
                'memo'        => 'Transfer from checking',
                'sic'         => '',
                'checkNumber' => ''
            ],
            [
                'type'        => 'CHECK',
                'typeDesc'    => 'Cheque',
                'amount'      => -10000,
                'uniqueId'    => '980309001',
                'name'        => 'Cheque',
                'memo'        => '',
                'sic'         => '',
                'checkNumber' => '1025'
            ],
        ];

        foreach ($transactions as $i => $transaction) {
            self::assertSame($expectedTransactions[$i]['type'], $transaction->getType());
            self::assertSame($expectedTransactions[$i]['typeDesc'], $transaction->getTypeDescription());
            self::assertSame($expectedTransactions[$i]['amount'], $transaction->getAmount());
            self::assertSame($expectedTransactions[$i]['uniqueId'], $transaction->getUniqueId());
            self::assertSame($expectedTransactions[$i]['name'], $transaction->getName());
            self::assertSame($expectedTransactions[$i]['memo'], $transaction->getMemo());
            self::assertSame($expectedTransactions[$i]['sic'], $transaction->getSic());
            self::assertSame($expectedTransactions[$i]['checkNumber'], $transaction->getCheckNumber());
        }
    }

    public function testICanCheckTheBankAccountDetails(): void
    {
        $bankAccount = $this->ofsContent->bankAccount;
        self::assertSame('23382938', $bankAccount->getTransactionUid());
        self::assertSame('098-121', $bankAccount->getAccountNumber());
        self::assertSame('987654321', $bankAccount->getRoutingNumber());
        self::assertSame('SAVINGS', $bankAccount->getAccountType());
        self::assertSame(525000, $bankAccount->getBalance());
        self::assertEquals(new \DateTime('2007-10-15T02:15:29.000000+0000'), $bankAccount->getBalanceDate());
    }

    public function testICanCheckTheBankAccountStatementDetails(): void
    {
        $statement = $this->ofsContent->bankAccount->getStatement();
        self::assertSame('USD', $statement->getCurrency());
        self::assertEquals(new \DateTime('2007-01-01T00:00:00.000000+0000'), $statement->getStartDate());
        self::assertEquals(new \DateTime('2007-10-15T00:00:00.000000+0000'), $statement->getEndDate());
    }
}
