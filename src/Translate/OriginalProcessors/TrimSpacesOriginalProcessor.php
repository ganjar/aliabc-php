<?php

namespace ALI\Translate\OriginalProcessors;

/**
 * Class ReplaceDuplicateSpacesOriginalProcessor
 * @package ALI\Translate\OriginalProcessors
 */
class TrimSpacesOriginalProcessor implements OriginalProcessorInterface
{
    /**
     * @param string $original
     * @return string
     */
    public function process($original)
    {
        return preg_replace('!\s+!s', ' ', trim($original));
    }
}