<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Entity;

use DateTime;

final class SignOn
{
    private Status $status;
    private DateTime $date;
    private string $language;
    private Institution $institution;

    public function __construct(
        Status $status,
        DateTime $date,
        string $language,
        Institution $institute
    ) {
        $this->status = $status;
        $this->date = $date;
        $this->language = $language;
        $this->institution = $institute;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getInstitution(): Institution
    {
        return $this->institution;
    }
}
