<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Entity;

final class Payee
{
    private string $name;
    private string $address1;
    private string $address2;
    private string $address3;
    private string $city;
    private string $state;
    private string $postalCode;
    private string $country;
    private string $phone;

    public function __construct(
        string $name,
        string $address1,
        string $address2,
        string $address3,
        string $city,
        string $state,
        string $postalCode,
        string $country,
        string $phone
    ) {
        $this->name = $name;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->address3 = $address3;
        $this->city = $city;
        $this->state = $state;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->phone = $phone;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress1(): string
    {
        return $this->address1;
    }

    public function getAddress2(): string
    {
        return $this->address2;
    }

    public function getAddress3(): string
    {
        return $this->address3;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }
}
