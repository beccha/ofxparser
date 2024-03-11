<?php

declare(strict_types=1);

namespace Beccha\OfxParser\Tests;

use Beccha\OfxParser\Exception\UnRecognisedDateFormat;
use Beccha\OfxParser\Parser;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testNoFileIsProvided(): void
    {
        $parser = new Parser();

        $this->expectException(InvalidArgumentException::class);
        $parser->loadFromFile('file/does/not.exists.xml');
    }

    /**
     * @throws Exception
     */
    public function testIGetAnExceptionWhenTheTransactionDateIsNotRecognisable(): void
    {
        $parser = new Parser();

        $this->expectException(UnRecognisedDateFormat::class);
        $parser->loadFromFile(__DIR__ . '/../fixtures/bad_date_format.ofx');
    }
}
