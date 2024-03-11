<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Exception;

use RuntimeException;

class OfxTagNotFoundException extends RuntimeException
{
    public function __construct(string $tag)
    {
        parent::__construct(sprintf('Tag "%s" not found', $tag));
    }
}
