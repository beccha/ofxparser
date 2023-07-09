<?php

declare(strict_types=1);

namespace Beccha\OfxParser;

use Beccha\OfxParser\Service\SgmlToXml;
use Exception;
use InvalidArgumentException;

class Parser
{
    /**
     * Load an OFX file into this parser by way of a filename
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function loadFromFile(string $ofxFile): Ofx
    {
        if (false === file_exists($ofxFile)) {
            throw new InvalidArgumentException("File '{$ofxFile}' could not be found");
        }

        return $this->loadFromString($ofxFile);
    }

    /**
     * Load an OFX by directly using the text content
     * @throws Exception
     */
    public function loadFromString(string $filePath): Ofx
    {
        $xmlFromSgml = new SgmlToXml();
        $xml = $xmlFromSgml->parse($filePath);

        return new Ofx($xml);
    }
}
