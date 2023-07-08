<?php

declare(strict_types=1);

namespace OfxParser;

class SgmlToXml
{
    public function parse(string $sgmlFilePath): \SimpleXMLElement
    {
        $fileContent = $this->loadFile($sgmlFilePath);

        // Check if file is already XML
        $isXml = $this->isXml($fileContent);
        if ($isXml) {
            return $this->getXmlContent($fileContent);
        }

        $contentWithoutSgmlHeader = $this->removeSgmlHeader($fileContent);

        // Fix unclosed tags
        $sgmlLines = $this->sgmlContentToArrayOfLines($contentWithoutSgmlHeader);
        $tagsWithoutContent = $this->listTagsWithoutContent($sgmlLines);
        $tagsToClose = $this->filterTagsThatHaveAClosingCouterpart($sgmlLines, $tagsWithoutContent);
        $tagsFixedForXml = $this->closeTags($sgmlLines, $tagsToClose);
        $xmlFileContent = $this->buildXmlFileContent($tagsFixedForXml);

        return $this->getXmlContent($xmlFileContent);
    }

    private function loadFile(string $sgmlFilePath)
    {
        $fileContent = file_get_contents($sgmlFilePath);
        $detectedEncoding = mb_detect_encoding($fileContent);
        return mb_convert_encoding($fileContent, "UTF-8", $detectedEncoding);
    }

    /**
     * Remove SGMLheader
     */
    private function removeSgmlHeader(string $sgmlFileContent): string
    {
        $upercasedContent = mb_convert_case($sgmlFileContent, MB_CASE_UPPER);

        $sgmlStart = stripos($upercasedContent, '<OFX>');
        return trim(substr($sgmlFileContent, $sgmlStart));
    }

    private function sgmlContentToArrayOfLines(string $sgmlFileContent): array
    {
        $trimmedLines = [];
        $lines = explode("\n", $sgmlFileContent);
        foreach ($lines as $line) {
            $trimmedLines[] = trim($line);
        }

        return $trimmedLines;
    }

    /**
     * Search for tags within a xml file without content
     */
    private function listTagsWithoutContent(array $linesFromSgml): array
    {
        $tagsWithoutContent = [];
        foreach ($linesFromSgml as $line) {
            $trimmedLine = trim($line);
            if (preg_match('/^<[a-z0-9\-_]*>$/i', $trimmedLine)) {
                $tagsWithoutContent[] = $trimmedLine;
            }
        }

        return $tagsWithoutContent;
    }

    /**
     * Within a xml file, filter out tags that have a closing couterpart
     */
    private function filterTagsThatHaveAClosingCouterpart(array $linesFromSgml, array $tagsWithoutContent): array
    {
        $tagsToClose = [];
        foreach ($tagsWithoutContent as $tag) {
            $tagWithoutClosing = str_replace('<', '</', $tag);
            if (!in_array($tagWithoutClosing, $linesFromSgml, true)) {
                $tagsToClose[] = $tag;
            }
        }
        return $tagsToClose;
    }

    /**
     * Within a xml file, close tags that are given in the array $tagsToClose
     */
    public function closeTags(array $linesFromSgml, array $emptyTagsToClose): array
    {
        $updatedLines = [];

        foreach ($linesFromSgml as $id => $tag) {
            $updatedLines[$id] = $tag;

            // Close empty tags that no closing counterpart and no data
            if (in_array($tag, $emptyTagsToClose, true)) {
                $updatedLines[$id] = $tag . str_replace('<', '</', $tag);
            }

            // Close tags that have no closing counterpart but have data
            $pattern = '/^(<[a-z0-9\-_]*>)(.+)$/i';

            if (preg_match($pattern, $tag, $openingTag)) {
                $closingTag = str_replace('<', '</', $openingTag[1]);

                // Only close tag if no clasing tag is found in the file
                if (!in_array($closingTag, $linesFromSgml, true)) {
                    $cleanedUpContent = $this->cleanUpContent($openingTag[2]);
                    $updatedLines[$id] = $openingTag[1] . $cleanedUpContent . $closingTag;
                }
            }
        }

        return $updatedLines;
    }

    private function cleanUpContent(string $content): string
    {
        return str_replace('&', '&amp;', $content);
    }

    private function buildXmlFileContent(array $linesFromSgml): string
    {
        array_unshift($linesFromSgml, '<?xml version="1.0" encoding="UTF-8"?>');
        return implode("\n", $linesFromSgml);
    }

    private function getXmlContent(string $fileContent)
    {
        return simplexml_load_string($fileContent);
    }

    private function isXml(string $fileContent): bool
    {
        libxml_use_internal_errors(true);
        $isXml = simplexml_load_string($fileContent);
        return $isXml !== false;
    }
}
