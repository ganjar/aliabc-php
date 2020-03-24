<?php

namespace ALI\Processors\TranslateProcessors;

use ALI\Translate\Translators\TranslatorInterface;

/**
 * This processor only replace all occurrences.
 * For example: you may replace image url from /logo.png to /ru_logo.png
 * Case sensitive search!
 * Class HardReplaceProcessor
 * @package ALI\Processors\TranslateProcessors
 */
class HardReplaceProcessor implements TranslateProcessors
{
    protected $replacements = [];

    /**
     * @return array
     */
    public function getReplacements()
    {
        return $this->replacements;
    }

    /**
     * @param array $replacements
     * @return $this
     */
    public function setReplacements(array $replacements)
    {
        $this->replacements = $replacements;

        return $this;
    }

    /**
     * @param $search
     * @param $replace
     * @return $this
     */
    public function addReplacement($search, $replace)
    {
        $this->replacements[$search] = $replace;

        return $this;
    }

    /**
     * @param string $content
     * @param string $cleanContent
     * @param TranslatorInterface $translator
     * @return string
     */
    public function process($content, $cleanContent, TranslatorInterface $translator)
    {
        return strtr($content, $this->getReplacements());
    }
}
