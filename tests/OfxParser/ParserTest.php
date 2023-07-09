<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Tests;

use Beccha\OfxParser\Parser;
use Exception;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testNoFileIsProvided(): void
    {
        $parser = new Parser();

        $this->expectException(\InvalidArgumentException::class);
        $parser->loadFromFile('file/does/not.exists.xml');
    }
}
