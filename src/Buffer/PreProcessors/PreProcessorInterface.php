<?php

namespace ALI\Buffer\PreProcessors;

/**
 * Interface PreProcessorInterface
 * @package ALI\PreProcessors
 */
interface PreProcessorInterface
{
    /**
     * @param string $content
     * @return string
     */
    public function process($content);
}