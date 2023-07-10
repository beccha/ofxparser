<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Entity;

final class Statement
{
    private string $currency;
    /**
     * @var array|Transaction[]
     */
    private array $transactions;
    private \DateTime $startDate;
    private \DateTime $endDate;

    /**
     * @param array|Transaction[] $transactions
     */
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

    /**
     * @return array|Transaction[]
     */
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
