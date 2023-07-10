<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Entity;

final class Status
{
    /**
     * @var array|string[]
     */
    protected array $codes = [
        '0'     => 'Success',
        '2000'  => 'General error',
        '15000' => 'Must change USERPASS',
        '15500' => 'Signon invalid',
        '15501' => 'Customer account already in use',
        '15502' => 'USERPASS Lockout'
    ];
    private string $code;
    private string $severity;
    private string $message;

    public function __construct(string $code, string $severity, string $message)
    {
        $this->code = $code;
        $this->severity = $severity;
        $this->message = $message;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDescription(): string
    {
        return $this->codes[$this->code] ?? '';
    }
}
