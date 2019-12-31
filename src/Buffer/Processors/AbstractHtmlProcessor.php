<?php

namespace ALI\Buffer\Processors;

/**
 * Class AbstractHtmlProcessor
 * @package ALI\Buffer\Processors
 */
abstract class AbstractHtmlProcessor extends ProcessorAbstract
{
    /**
     * @param string $buffer
     * @param string $cleanBuffer
     * @return string
     * @throws \ALI\Exceptions\ALIException
     */
    public function process($buffer, $cleanBuffer)
    {
        //todo - how to use $cleanBuffer
        preg_match_all($this->getFindPhrasesRegex(), $buffer, $match, PREG_OFFSET_CAPTURE);

        $forTranslate = [];
        foreach ($match['original'] as $originalData) {
            $forTranslate[] = $originalData[0];
        }

        $translateData = $this->getTranslate()->translateAll($forTranslate);

        $positionOffset = 0;
        foreach ($match['original'] AS $originalData) {

            $original = $originalData[0];
            $position = $originalData[1] + $positionOffset;

            //don't replace if we don't have translation
            if (empty($translateData[$original])) {
                continue;
            }

            $translate = htmlspecialchars($translateData[$original], ENT_QUOTES);

            //replace original to translate phrase
            $originalLen = strlen($original);
            $buffer = substr_replace($buffer, $translate, $position, $originalLen);

            $positionOffset += strlen($translate) - $originalLen;
        }

        return $buffer;
    }

    /**
     * Get RegEx for parse HTML and get all phrases for translate
     * @return string
     */
    abstract public function getFindPhrasesRegex();
}