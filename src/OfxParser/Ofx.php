<?php

namespace OfxParser;

use Exception;
use OfxParser\Entity\BankAccount;
use OfxParser\Entity\Institute;
use OfxParser\Entity\SignOn;
use OfxParser\Entity\Statement;
use OfxParser\Entity\Status;
use OfxParser\Entity\Transaction;
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
    public $header;
    public $signOn;
    public array $bankAccounts = [];

    /**
     * @param SimpleXMLElement $xml
     * @throws Exception
     */
    public function __construct(SimpleXMLElement $xml)
    {
        $this->signOn = $this->buildSignOn($xml->SIGNONMSGSRSV1->SONRS);
        $this->bankAccounts = $this->buildBankAccounts($xml);
    }

    /**
     * @return array|BankAccount[]
     */
    public function getBankAccounts(): array
    {
        return $this->bankAccounts;
    }

    /**
     * @param $xml
     * @return SignOn
     * @throws Exception
     */
    private function buildSignOn($xml)
    {
        $SignOn = new SignOn();
        $SignOn->status = $this->buildStatus($xml->STATUS);
        $SignOn->date = $this->createDateTimeFromStr($xml->DTSERVER, true);
        $SignOn->language = $xml->LANGUAGE;

        $SignOn->institute = new Institute();
        $SignOn->institute->name = $xml->FI->ORG;
        $SignOn->institute->id = $xml->FI->FID;

        return $SignOn;
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
            $xml->STMTRS->BANKACCTFROM->BRANCHID,
            $xml->STMTRS->BANKACCTFROM->ACCTID,
            $xml->STMTRS->BANKACCTFROM->ACCTTYPE,
            (float)($xml->STMTRS->LEDGERBAL->BALAMT),
            $xml->STMTRS->LEDGERBAL->DTASOF ? $this->createDateTimeFromStr(
                $xml->STMTRS->LEDGERBAL->DTASOF,
                true
            ) : '',
            $xml->STMTRS->BANKACCTFROM->BANKID,
            $this->buildStatement($xml),
            $xml->TRNUID
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
            $this->createDateTimeFromStr($xml->STMTRS->BANKTRANLIST->DTSTART),
            $this->createDateTimeFromStr($xml->STMTRS->BANKTRANLIST->DTEND)
        );
    }

    /**
     * @throws Exception
     */
    private function buildTransactions(SimpleXMLElement $transactions): array
    {
        $transactionEntities = [];
        foreach ($transactions as $t) {
            $transaction = new Transaction(
                (string)$t->TRNTYPE,
                ($this->createDateTimeFromStr((string)$t->DTPOSTED)),
                $this->createAmountFromStr((string)$t->TRNAMT),
                (string)$t->FITID,
                (string)$t->NAME,
                (string)$t->MEMO,
                (string)$t->SIC,
                (string)$t->CHECKNUM
            );

            $transactionEntities[] = $transaction;
        }

        return $transactionEntities;
    }

    private function buildStatus($xml): Status
    {
        return new Status(
            (string)$xml->CODE,
            (string)$xml->SEVERITY,
            (string)$xml->MESSAGE
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
    private function createDateTimeFromStr(string $dateString, bool $ignoreErrors = false): ?\DateTime
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

            try {
                return new \DateTime($format);
            } catch (Exception $e) {
                if ($ignoreErrors) {
                    return null;
                }

                throw $e;
            }
        }

        throw new Exception("Failed to initialize DateTime for string: " . $dateString);
    }

    /**
     * Create a formated number in Float according to different locale options
     *
     * Supports:
     * 000,00 and -000,00
     * 0.000,00 and -0.000,00
     * 0,000.00 and -0,000.00
     * 000.00 and 000.00
     *
     * @param string $amountString
     * @return float
     */
    private function createAmountFromStr($amountString)
    {
        //000.00 or 0,000.00
        if (preg_match("/^-?([0-9,]+)(\.?)([0-9]{2})$/", $amountString) == 1) {
            $amountString = preg_replace(
                array("/([,]+)/",
                      "/\.?([0-9]{2})$/"
                ),
                array("", ".$1"),
                $amountString
            );
        } elseif (preg_match("/^-?([0-9\.]+,?[0-9]{2})$/", $amountString) == 1) {//000,00 or 0.000,00
            $amountString = preg_replace(
                array("/([\.]+)/",
                      "/,?([0-9]{2})$/"
                ),
                array("",
                      ".$1"),
                $amountString
            );
        }

        return (float)$amountString;
    }
}
