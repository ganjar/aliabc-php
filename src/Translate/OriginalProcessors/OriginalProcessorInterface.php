<?php

namespace ALI\Translate\OriginalProcessors;

/**
 * Interface OriginalProcessorInterface
 * @package ALI\Translate\OriginalProcessors
 */
interface OriginalProcessorInterface
{
    /**
     * @param string $original
     * @return string
     */
    public function process($original);
}