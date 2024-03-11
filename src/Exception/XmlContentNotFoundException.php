<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Exception;

use RuntimeException;

class XmlContentNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('XML content not found');
    }
}
