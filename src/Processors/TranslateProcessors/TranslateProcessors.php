<?php

namespace ALI\Processors\TranslateProcessors;

use ALI\Translate\Translators\TranslatorInterface;

/**
 * Interface TranslateProcessors
 */
interface TranslateProcessors
{
    /**
     * @param string $content
     * @param string $cleanContent
     * @param TranslatorInterface $translator
     * @return string
     */
    public function process($content, $cleanContent, TranslatorInterface $translator);
}
