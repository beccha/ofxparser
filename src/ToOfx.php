<?php

declare(strict_types=1);

namespace Beccha\OfxParser;

use Beccha\OfxParser\Entity\BankAccount;
use Beccha\OfxParser\Entity\SignOn;
use XMLWriter;

class ToOfx
{
    /**
     * @var array<BankAccount>
     */
    private array $banks;
    private SignOn $signOn;

    /**
     * @param array<BankAccount> $banks
     */
    public function __construct(SignOn $signOn, array $banks)
    {
        $this->banks = $banks;
        $this->signOn = $signOn;
    }

    public function generate(): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8', 'no');
        $writer->setIndent(true);
        $writer->setIndentString(' ');

        // Write OFX declaration
        $writer->writeRaw(
            '<?OFX'
            . ' OFXHEADER="200"'
            . ' VERSION="211"'
            . ' SECURITY="NONE"'
            . ' OLDFILEUID="NONE"'
            . ' NEWFILEUID="12345678901234567890123456789012"?>'
            . "\n"
        );

        $writer->startElement('OFX');
        $this->createSignOn($writer);

        // Banks
        foreach ($this->banks as $bank) {
            $writer->startElement('BANKMSGSRSV1');
            $writer->startElement('STMTTRNRS');
            $writer->startElement('TRNUID');
            $writer->text($bank->getTransactionUid());
            $writer->endElement(); // TRNUID
            $writer->startElement('STMTRS');
            $writer->startElement('CURDEF');
            $writer->text($bank->getStatement()->getCurrency());
            $writer->endElement(); // CURDEF
            $this->createBankAccountFrom($writer, $bank);
            $writer->startElement('BANKTRANLIST');
            $writer->startElement('DTSTART');
            $writer->text($bank->getStatement()->getStartDate()->format('YmdHis'));
            $writer->endElement(); // DTSTART
            $writer->startElement('DTEND');
            $writer->text($bank->getStatement()->getEndDate()->format('YmdHis'));
            $writer->endElement(); // DTEND
            foreach ($bank->getStatement()->getTransactions() as $transaction) {
                $this->createTransaction($writer, $transaction);
            }
            $writer->endElement(); // BANKTRANLIST
            $this->createLedgerBalance($writer, $bank);
            $writer->endElement(); // STMTRS
            $writer->endElement(); // STMTTRNRS
            $writer->endElement(); // BANKMSGSRSV1
        }

        $writer->endElement(); // OFX
        $writer->endDocument();

        return $writer->outputMemory(true);
    }

    private function createTransaction(XMLWriter $writer, Entity\Transaction $transaction): void
    {
        $writer->startElement('STMTTRN');
        $writer->startElement('TRNTYPE');
        $writer->text($transaction->getType());
        $writer->endElement(); // TRNTYPE
        $writer->startElement('DTPOSTED');
        $writer->text($transaction->getDate()->format('YmdHis'));
        $writer->endElement(); // DTPOSTED
        $writer->startElement('TRNAMT');
        $writer->text($this->formatAmount($transaction->getAmount()));
        $writer->endElement(); // TRNAMT
        $writer->startElement('FITID');
        $writer->text($transaction->getUniqueId());
        $writer->endElement(); // FITID
        $writer->startElement('NAME');
        $writer->text($transaction->getName());
        $writer->endElement(); // NAME
        $writer->startElement('MEMO');
        $writer->text($transaction->getMemo());
        $writer->endElement(); // NAME
        $writer->endElement(); // STMTTRN
    }

    private function createSignOn(XMLWriter $writer): void
    {
// SignOn
        $writer->startElement('SIGNONMSGSRSV1');
        $writer->startElement('SONRS');
        $this->createServerStatus($writer);
        $writer->startElement('DTSERVER');
        $writer->text($this->signOn->getDate()->format('YmdHis'));
        $writer->endElement(); // DTSERVER
        $writer->startElement('LANGUAGE');
        $writer->text($this->signOn->getLanguage());
        $writer->endElement(); // LANGUAGE
        $this->createSignOnFi($writer);
        $writer->endElement(); // SONRS
        $writer->endElement(); // SIGNONMSGSRSV1
    }

    private function createSignOnFi(XMLWriter $writer): void
    {
        $writer->startElement('FI');
        $writer->startElement('ORG');
        $writer->text($this->signOn->getInstitution()->getName());
        $writer->endElement(); // ORG
        $writer->startElement('FID');
        $writer->text($this->signOn->getInstitution()->getId());
        $writer->endElement(); // FID
        $writer->endElement(); // FI
    }

    private function createServerStatus(XMLWriter $writer): void
    {
        $writer->startElement('STATUS');
        $writer->startElement('CODE');
        $writer->text($this->signOn->getStatus()->getCode());
        $writer->endElement(); // CODE
        $writer->startElement('SEVERITY');
        $writer->text($this->signOn->getStatus()->getSeverity());
        $writer->endElement(); // SEVERITY
        $writer->startElement('MESSAGE');
        $writer->text($this->signOn->getStatus()->getMessage());
        $writer->endElement(); // MESSAGE
        $writer->endElement(); // STATUS
    }

    private function createLedgerBalance(XMLWriter $writer, BankAccount $bank): void
    {
        $writer->startElement('LEDGERBAL');
        $writer->startElement('BALAMT');
        $writer->text($this->formatAmount($bank->getBalance()));
        $writer->endElement(); // BALAMT
        $writer->startElement('DTASOF');
        $writer->text($bank->getBalanceDate()->format('YmdHis'));
        $writer->endElement(); // DTASOF
        $writer->endElement(); // LEDGERBAL
    }

    /**
     * @param XMLWriter $writer
     * @param BankAccount $bank
     * @return void
     */
    private function createBankAccountFrom(XMLWriter $writer, BankAccount $bank): void
    {
        $writer->startElement('BANKACCTFROM');
        $writer->startElement('BANKID');
        $writer->text($bank->getRoutingNumber());
        $writer->endElement(); // BANKID
        $writer->startElement('ACCTID');
        $writer->text($bank->getAccountNumber());
        $writer->endElement(); // ACCTID
        $writer->startElement('ACCTTYPE');
        $writer->text($bank->getAccountType());
        $writer->endElement(); // ACCTTYPE
        $writer->endElement(); // BANKACCTFROM
    }

    private function formatAmount(int $amount): string
    {
        return number_format(($amount / 100), 2, '.', '');
    }

    /**
     * @param XMLWriter $writer
     * @return void
     */
    private function bankStatus(XMLWriter $writer): void
    {
        $writer->startElement('STATUS');
        $writer->startElement('CODE');
        $writer->text('');
        $writer->endElement(); // CODE
        $writer->startElement('SEVERITY');
        $writer->text('');
        $writer->endElement(); // SEVERITY
        $writer->startElement('MESSAGE');
        $writer->text('');
        $writer->endElement(); // MESSAGE
        $writer->endElement(); // STATUS
    }

    /**
     * @param XMLWriter $writer
     * @return void
     */
    private function createSignOnFi(XMLWriter $writer): void
    {
        $writer->startElement('FI');
        $writer->startElement('ORG');
        $writer->text($this->signOn->getInstitution()->getName());
        $writer->endElement(); // ORG
        $writer->startElement('FID');
        $writer->text($this->signOn->getInstitution()->getId());
        $writer->endElement(); // FID
        $writer->endElement(); // FI
    }

    /**
     * @param XMLWriter $writer
     * @return void
     */
    private function createServerStatus(XMLWriter $writer): void
    {
        $writer->startElement('STATUS');
        $writer->startElement('CODE');
        $writer->text($this->signOn->getStatus()->getCode());
        $writer->endElement(); // CODE
        $writer->startElement('SEVERITY');
        $writer->text($this->signOn->getStatus()->getSeverity());
        $writer->endElement(); // SEVERITY
        $writer->startElement('MESSAGE');
        $writer->text($this->signOn->getStatus()->getMessage());
        $writer->endElement(); // MESSAGE
        $writer->endElement(); // STATUS
    }

    /**
     * @param XMLWriter $writer
     * @return void
     */
    private function createLedgerBalance(XMLWriter $writer): void
    {
        $writer->startElement('LEDGERBAL');
        $writer->startElement('BALAMT');
        $writer->text('');
        $writer->endElement(); // BALAMT
        $writer->startElement('DTASOF');
        $writer->text('');
        $writer->endElement(); // DTASOF
        $writer->endElement(); // LEDGERBAL
    }

    /**
     * @param XMLWriter $writer
     * @param $bank
     * @return void
     */
    private function createBankAccountFrom(XMLWriter $writer, $bank): void
    {
        $writer->startElement('BANKACCTFROM');
        $writer->startElement('BANKID');
        $writer->text('');
        $writer->endElement(); // BANKID
        $writer->startElement('ACCTID');
        $writer->text($bank->getAccountNumber());
        $writer->endElement(); // ACCTID
        $writer->startElement('ACCTTYPE');
        $writer->text($bank->getAccountType());
        $writer->endElement(); // ACCTTYPE
        $writer->endElement(); // BANKACCTFROM
    }
}
