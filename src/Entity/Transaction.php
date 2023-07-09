<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Entity;

final class Transaction
{
    private array $types = array(
        "CREDIT"      => "Generic credit",
        "DEBIT"       => "Generic debit",
        "INT"         => "Interest earned or paid ",
        "DIV"         => "Dividend",
        "FEE"         => "FI fee",
        "SRVCHG"      => "Service charge",
        "DEP"         => "Deposit",
        "ATM"         => "ATM debit or credit",
        "POS"         => "Point of sale debit or credit ",
        "XFER"        => "Transfer",
        "CHECK"       => "Cheque",
        "PAYMENT"     => "Electronic payment",
        "CASH"        => "Cash withdrawal",
        "DIRECTDEP"   => "Direct deposit",
        "DIRECTDEBIT" => "Merchant initiated debit",
        "REPEATPMT"   => "Repeating payment/standing order",
        "OTHER"       => "Other"
    );
    private string $type;
    private \DateTime $date;
    private int $amount;
    private string $uniqueId;
    private string $name;
    private string $memo;
    private string $sic;
    private string $checkNumber;
    private Payee $payee;

    public function __construct(
        string $type,
        \DateTime $date,
        float $amount,
        string $uniqueId,
        string $name,
        string $memo,
        string $sic,
        string $checkNumber,
        Payee $payee
    ) {
        $this->type = $type;
        $this->date = $date;
        $this->amount = (int)($amount * 100);
        $this->uniqueId = $uniqueId;
        $this->name = $name;
        $this->memo = $memo;
        $this->sic = $sic;
        $this->checkNumber = $checkNumber;
        $this->payee = $payee;
    }

    public function getPayee(): Payee
    {
        return $this->payee;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMemo(): string
    {
        return $this->memo;
    }

    public function getSic(): string
    {
        return $this->sic;
    }

    public function getCheckNumber(): string
    {
        return $this->checkNumber;
    }

    public function getTypeDescription(): string
    {
        return $this->types[$this->type] ?? '';
    }
}
