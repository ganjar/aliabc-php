<?php

namespace ALI\Translate\OriginalProcessors;


/**
 * Class HtmlEntityDecodeOriginalProcessor
 * @package ALI\Translate\OriginalProcessors
 */
class HtmlEntityDecodeOriginalProcessor implements OriginalProcessorInterface
{
    protected $charset;

    /**
     * HtmlEntityDecodeOriginalProcessor constructor.
     * @param $charset
     */
    public function __construct($charset = 'UTF-8')
    {
        $this->charset = $charset;
    }

    /**
     * @param string $original
     * @return string
     */
    public function process($original)
    {
        return html_entity_decode($original, ENT_QUOTES, $this->charset);
    }
}