<?php

declare(strict_types=1);

namespace Beccha\OfxParser;

use Beccha\OfxParser\Entity\BankAccount;
use Beccha\OfxParser\Entity\Institution;
use Beccha\OfxParser\Entity\Payee;
use Beccha\OfxParser\Entity\SignOn;
use Beccha\OfxParser\Entity\Statement;
use Beccha\OfxParser\Entity\Status;
use Beccha\OfxParser\Entity\Transaction;
use Beccha\OfxParser\Exception\UnRecognisedDateFormat;
use Exception;
use SimpleXMLElement;

/**
 * The OFX object
 *
 * Heavily refactored from Guillaume Bailleul's grimfor/ofxparser
 *
 * Second refactor by Oliver Lowe to unify the API across all
 * OFX data-types.
 *
 * Based on Andrew A Smith's Ruby ofx-parser
 *
 * @author Guillaume BAILLEUL <contact@guillaume-bailleul.fr>
 * @author James Titcumb <hello@jamestitcumb.com>
 * @author Oliver Lowe <mrtriangle@gmail.com>
 */
class Ofx
{
    private SignOn $signOn;

    /**
     * @var array|BankAccount[]
     */
    private array $bankAccounts;

    /**
     * @param SimpleXMLElement $xml
     * @throws Exception
     */
    public function __construct(SimpleXMLElement $xml)
    {
        $this->signOn = $this->buildSignOn($xml->SIGNONMSGSRSV1->SONRS);
        $this->bankAccounts = $this->buildBankAccounts($xml);
    }

    public function getSignOn(): SignOn
    {
        return $this->signOn;
    }

    /**
     * @return array|BankAccount[]
     */
    public function getBankAccounts(): array
    {
        return $this->bankAccounts;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return SignOn
     * @throws Exception
     */
    private function buildSignOn(SimpleXMLElement $xml): SignOn
    {
        $institute = new Institution(
            (string)$xml->FI->FID,
            (string)$xml->FI->ORG
        );

        return new SignOn(
            $this->buildStatus($xml->STATUS),
            $this->createDateTimeFromStr((string)$xml->DTSERVER),
            (string)$xml->LANGUAGE,
            $institute
        );
    }

    /**
     * @param SimpleXMLElement $xml
     * @return array<BankAccount>
     * @throws Exception
     */
    private function buildBankAccounts(SimpleXMLElement $xml): array
    {
        // Loop through the bank accounts
        $bankAccounts = [];
        foreach ($xml->BANKMSGSRSV1->STMTTRNRS as $accountStatement) {
            $bankAccounts[] = $this->buildBankAccount($accountStatement);
        }
        return $bankAccounts;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return BankAccount
     * @throws Exception
     */
    private function buildBankAccount(SimpleXMLElement $xml): BankAccount
    {
        return new BankAccount(
            (string)$xml->STMTRS->BANKACCTFROM->BRANCHID,
            (string)$xml->STMTRS->BANKACCTFROM->ACCTID,
            (string)$xml->STMTRS->BANKACCTFROM->ACCTTYPE,
            (float)($xml->STMTRS->LEDGERBAL->BALAMT),
            $xml->STMTRS->LEDGERBAL->DTASOF ? $this->createDateTimeFromStr(
                (string)$xml->STMTRS->LEDGERBAL->DTASOF
            ) : '',
            (string)$xml->STMTRS->BANKACCTFROM->BANKID,
            $this->buildStatement($xml),
            (string)$xml->TRNUID
        );
    }

    /**
     * @throws Exception
     */
    private function buildStatement(SimpleXMLElement $xml): Statement
    {
        return new Statement(
            (string)$xml->STMTRS->CURDEF,
            $this->buildTransactions($xml->STMTRS->BANKTRANLIST->STMTTRN),
            $this->createDateTimeFromStr((string)$xml->STMTRS->BANKTRANLIST->DTSTART),
            $this->createDateTimeFromStr((string)$xml->STMTRS->BANKTRANLIST->DTEND)
        );
    }

    /**
     * @return array<Transaction>
     * @throws Exception
     */
    private function buildTransactions(SimpleXMLElement $transactions): array
    {
        $transactionEntities = [];
        foreach ($transactions as $t) {
            $payee = $this->buildPayee($t->PAYEE);

            $transaction = new Transaction(
                (string)$t->TRNTYPE,
                ($this->createDateTimeFromStr((string)$t->DTPOSTED)),
                (float)$t->TRNAMT,
                (string)$t->FITID,
                (string)$t->NAME,
                (string)$t->MEMO,
                (string)$t->SIC,
                (string)$t->CHECKNUM,
                $payee
            );

            $transactionEntities[] = $transaction;
        }

        return $transactionEntities;
    }

    private function buildStatus(SimpleXMLElement $xml): Status
    {
        return new Status(
            (string)$xml->CODE,
            (string)$xml->SEVERITY,
            (string)$xml->MESSAGE
        );
    }

    private function buildPayee(SimpleXMLElement $xml): Payee
    {
        return new Payee(
            (string)$xml->NAME,
            (string)$xml->ADDR1,
            (string)$xml->ADDR2,
            (string)$xml->ADDR3,
            (string)$xml->CITY,
            (string)$xml->STATE,
            (string)$xml->POSTALCODE,
            (string)$xml->COUNTRY,
            (string)$xml->PHONE
        );
    }

    /**
     * Create a DateTime object from a valid OFX date format
     *
     * Supports:
     * YYYYMMDDHHMMSS.XXX[gmt offset:tz name]
     * YYYYMMDDHHMMSS.XXX
     * YYYYMMDDHHMMSS
     * YYYYMMDD
     * YYYY-MM-DD
     * @throws Exception
     */
    private function createDateTimeFromStr(string $dateString): \DateTime
    {
        $regex = "/"
            . "(\d{4})[-]?(\d{2})[-]?(\d{2})?" // YYYYMMDD   YYYY-MM-DD          1,2,3
            . "(?:(\d{2})(\d{2})(\d{2}))?" // HHMMSS   - optional  4,5,6
            . "(?:\.(\d{3}))?" // .XXX     - optional  7
            . "(?:\[(-?\d+)\:(\w{3}\]))?" // [-n:TZ]  - optional  8,9
            . "/";

        if (preg_match($regex, $dateString, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];
            $hour = isset($matches[4]) ? (int)$matches[4] : 0;
            $min = isset($matches[5]) ? (int)$matches[5] : 0;
            $sec = isset($matches[6]) ? (int)$matches[6] : 0;

            $format = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $min . ':' . $sec;

            return new \DateTime($format);
        }
        throw new UnRecognisedDateFormat($dateString);
    }
}
