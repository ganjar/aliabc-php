<?php

namespace ALI\Translate\TranslateProcessors;

/**
 * Interface TranslateProcessorInterface
 * @package ALI\Translate\TranslateProcessors
 */
interface TranslateProcessorInterface
{
    /**
     * @param string $original
     * @param string $translate
     * @return string - translate string
     */
    public function process($original, $translate);
}