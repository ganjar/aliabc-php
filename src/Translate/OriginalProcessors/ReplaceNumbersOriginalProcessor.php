<?php

namespace ALI\Translate\OriginalProcessors;


/**
 * Class ReplaceNumbersOriginalProcessor
 * @package ALI\Buffer\PreProcessors
 */
class ReplaceNumbersOriginalProcessor implements OriginalProcessorInterface
{

    /**
     * @param string $original
     * @return string
     */
    public function process($original)
    {
        return preg_replace('!\d+!', '0', $original);
    }
}