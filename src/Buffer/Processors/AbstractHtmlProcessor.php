<?php

namespace ALI\Buffer\Processors;

/**
 * Class AbstractHtmlProcessor
 * @package ALI\Buffer\Processors
 */
abstract class AbstractHtmlProcessor extends ProcessorAbstract
{
    /**
     * Be careful if you want to change this parameter to false.
     * Without html entity encode you can break HTML syntax.
     * It can also be the cause of malicious JS code on the site.
     * Change to false only if you fully trust the translation source.
     * @var bool
     */
    protected $applyHtmlEntityEncode = true;

    /**
     * @param string $buffer
     * @param string $cleanBuffer
     * @return string
     * @throws \ALI\Exceptions\ALIException
     */
    public function process($buffer, $cleanBuffer)
    {
        if ($this->getTranslate()->getLanguage()->getIsOriginal()) {
            return $buffer;
        }

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

            $translate = $translateData[$original];

            if ($this->isApplyHtmlEntityEncode()) {
                $translate = htmlspecialchars($translate, ENT_QUOTES, 'UTF-8', false);
            }

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

    /**
     * @return bool
     */
    public function isApplyHtmlEntityEncode()
    {
        return $this->applyHtmlEntityEncode;
    }

    /**
     * Be careful if you want to change this parameter to false.
     * Without html entity encode you can break HTML syntax.
     * It can also be the cause of malicious JS code on the site.
     * Change to false only if you fully trust the translation source.
     * @param bool $applyHtmlEntityEncode
     * @return $this
     */
    public function setApplyHtmlEntityEncode($applyHtmlEntityEncode)
    {
        $this->applyHtmlEntityEncode = $applyHtmlEntityEncode;

        return $this;
    }
}