<?php

namespace OfxParser\Entity;

class Status extends AbstractEntity
{
    protected array $codes = [
        '0' => 'Success',
        '2000' => 'General error',
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
        $this->code = (string)$code;
        $this->severity = (string)$severity;
        $this->message = (string)$message;
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
        // Cast code to string from SimpleXMLObject
        $code = (string)$this->code;
        return $this->codes[$code] ?? '';
    }

    /** @deprecated */
    public function codeDesc(): string
    {
        return $this->getDescription();
    }
}
