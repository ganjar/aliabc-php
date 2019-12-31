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
        preg_match_all($this->getFindPhrasesRegex(), $cleanBuffer, $match);
        $originalData = [
            'match'    => $match[0],
            'original' => $match['original'],
        ];

        $pos = 0;
        $translateData = $this->getTranslate()->translateAll($originalData['original']);

        foreach ($originalData['original'] AS $k => $original) {

            //find original phrase position
            $pos = strpos($buffer, $originalData['match'][$k], $pos);
            preg_match($this->getFindPhrasesRegex(), $buffer, $matchPosition, PREG_OFFSET_CAPTURE, $pos);
            $pos = $matchPosition['original'][1];

            //don't replace if we don't have translation
            if (empty($translateData[$original])) {
                continue;
            }

            $translate = htmlspecialchars($translateData[$original], ENT_QUOTES);

            //replace original to translate phrase
            $buffer = substr_replace($buffer, $translate, $pos, strlen($original));
        }

        return $buffer;
    }

    /**
     * Get RegEx for parse HTML and get all phrases for translate
     * @return string
     */
    abstract public function getFindPhrasesRegex();
}