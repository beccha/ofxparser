<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Exception;

use RuntimeException;

class FileNotFoundException extends RuntimeException
{
    public function __construct(string $filePath)
    {
        parent::__construct(sprintf('File "%s" not found', $filePath));
    }
}
