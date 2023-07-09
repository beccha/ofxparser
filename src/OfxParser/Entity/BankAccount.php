<?php

namespace OfxParser\Entity;

final class BankAccount extends AbstractEntity
{
    private string $agencyNumber;
    private string $accountNumber;
    private string $accountType;
    private int $balance;
    private \DateTime $balanceDate;
    private string $routingNumber;
    private Statement $statement;
    private string $transactionUid;

    public function __construct(
        string $agencyNumber,
        string $accountNumber,
        string $accountType,
        float $balance,
        \DateTime $balanceDate,
        string $routingNumber,
        Statement $statement,
        string $transactionUid
    ) {
        $this->agencyNumber = $agencyNumber;
        $this->accountNumber = $accountNumber;
        $this->accountType = $accountType;
        $this->balance = $balance * 100;
        $this->balanceDate = $balanceDate;
        $this->routingNumber = $routingNumber;
        $this->statement = $statement;
        $this->transactionUid = $transactionUid;
    }

    public function getAgencyNumber(): string
    {
        return $this->agencyNumber;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getAccountType(): string
    {
        return $this->accountType;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function getBalanceDate(): \DateTime
    {
        return $this->balanceDate;
    }

    public function getRoutingNumber(): string
    {
        return $this->routingNumber;
    }

    public function getStatement(): Statement
    {
        return $this->statement;
    }

    public function getTransactionUid(): string
    {
        return $this->transactionUid;
    }
}
