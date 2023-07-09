<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Exception;

class UnRecognisedDateFormat extends \RuntimeException
{
    public function __construct(string $dateString)
    {
        parent::__construct(sprintf("Failed to initialize DateTime for string: %s", $dateString));
    }
}
