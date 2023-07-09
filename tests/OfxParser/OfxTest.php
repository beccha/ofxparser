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
        $signOn = $this->ofsContent->getSignOn();

        self::assertEquals('', $signOn->getStatus()->getMessage());
        self::assertEquals('0', $signOn->getStatus()->getCode());
        self::assertEquals('INFO', $signOn->getStatus()->getSeverity());
        self::assertEquals('Success', $signOn->getStatus()->getDescription());

        self::assertEquals('ENG', $signOn->getLanguage());
        self::assertEquals('MYBANK', $signOn->getInstitution()->getName());
        self::assertEquals('01234', $signOn->getInstitution()->getId());
    }

    /**
     * @throws Exception
     */
    public function testBuildsMultipleBankAccounts(): void
    {
        $multiOfxFile = dirname(__DIR__) . '/fixtures/ofx-multiple-accounts-xml.ofx';
        $multiOfxData = simplexml_load_string(file_get_contents($multiOfxFile));
        $ofx = new Ofx($multiOfxData);

        self::assertCount(3, $ofx->getBankAccounts());
    }

    public function testICanCheckDetailsOfTransactions(): void
    {
        $bankAccount = $this->ofsContent->getBankAccounts();
        $firstBankAccount = $bankAccount[0];
        $transactions = $firstBankAccount->getStatement()->getTransactions();

        $expectedTransactions = [
            [
                'type'        => 'CREDIT',
                'typeDesc'    => 'Generic credit',
                'amount'      => 20000,
                'uniqueId'    => '980315001',
                'name'        => 'DEPOSIT',
                'memo'        => 'automatic deposit',
                'sic'         => '',
                'checkNumber' => '',
                'payeeid'     => '12345',
                'payee'       => [
                    'name'       => 'Company Name',
                    'address1'   => '123 Main Street',
                    'address2'   => 'Anytown, USA',
                    'address3'   => '',
                    'city'       => 'Anytown',
                    'state'      => 'CA',
                    'postalCode' => '98765',
                    'country'    => 'USA',
                    'phone'      => '123-456-7890'
                ]
            ],
            [
                'type'        => 'CREDIT',
                'typeDesc'    => 'Generic credit',
                'amount'      => 15000,
                'uniqueId'    => '980310001',
                'name'        => 'TRANSFER',
                'memo'        => 'Transfer from checking',
                'sic'         => '',
                'checkNumber' => '',
                'payeeid'     => '4321',
                'payee'       => [
                    'name'       => 'The best company',
                    'address1'   => '123 Broad Street',
                    'address2'   => '',
                    'address3'   => '',
                    'city'       => 'Thetown',
                    'state'      => 'CA',
                    'postalCode' => '15423',
                    'country'    => 'USA',
                    'phone'      => '123-444-7890'
                ]
            ],
            [
                'type'        => 'CHECK',
                'typeDesc'    => 'Cheque',
                'amount'      => -10000,
                'uniqueId'    => '980309001',
                'name'        => 'Cheque',
                'memo'        => '',
                'sic'         => '',
                'checkNumber' => '1025',
                'payeeid'     => '85423',
                'payee'       => [
                    'name'       => 'Another company',
                    'address1'   => '123 Broad Street',
                    'address2'   => 'Another',
                    'address3'   => '',
                    'city'       => 'Anytown',
                    'state'      => 'CA',
                    'postalCode' => '12457',
                    'country'    => 'USA',
                    'phone'      => '123-456-45121'
                ]
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

            self::assertSame($expectedTransactions[$i]['payee']['name'], $transaction->getPayee()->getName());
        }
    }

    public function testICanCheckTheBankAccountDetails(): void
    {
        $bankAccount = $this->ofsContent->getBankAccounts();
        $firstBankAccount = $bankAccount[0];

        self::assertSame('23382938', $firstBankAccount->getTransactionUid());
        self::assertSame('098-121', $firstBankAccount->getAccountNumber());
        self::assertSame('987654321', $firstBankAccount->getRoutingNumber());
        self::assertSame('SAVINGS', $firstBankAccount->getAccountType());
        self::assertSame(525000, $firstBankAccount->getBalance());
        self::assertEquals(new \DateTime('2007-10-15T02:15:29.000000+0000'), $firstBankAccount->getBalanceDate());
    }

    public function testICanCheckTheBankAccountStatementDetails(): void
    {
        $bankAccount = $this->ofsContent->getBankAccounts();
        $firstBankAccount = $bankAccount[0];
        $statement = $firstBankAccount->getStatement();

        self::assertSame('USD', $statement->getCurrency());
        self::assertEquals(new \DateTime('2007-01-01T00:00:00.000000+0000'), $statement->getStartDate());
        self::assertEquals(new \DateTime('2007-10-15T00:00:00.000000+0000'), $statement->getEndDate());
    }
}
