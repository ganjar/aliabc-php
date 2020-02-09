<?php

namespace ALI\Translate\OriginalProcessors;


/**
 * Class HtmlEntityDecodeOriginalProcessor
 * @package ALI\Translate\OriginalProcessors
 */
class HtmlEntityDecodeOriginalProcessor implements OriginalProcessorInterface
{

    /**
     * @param string $original
     * @return string
     */
    public function process($original)
    {
        return html_entity_decode($original, ENT_QUOTES);
    }
}