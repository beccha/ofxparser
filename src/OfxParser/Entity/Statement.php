<?php

namespace OfxParser\Entity;

final class Statement extends AbstractEntity
{
    private string $currency;
    private array $transactions;
    private \DateTime $startDate;
    private \DateTime $endDate;

    public function __construct(
        string $currency,
        array $transactions,
        \DateTime $startDate,
        \DateTime $endDate
    ) {
        $this->currency = $currency;
        $this->transactions = $transactions;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }
}
