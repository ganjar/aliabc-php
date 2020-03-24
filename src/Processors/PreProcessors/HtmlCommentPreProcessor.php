<?php

namespace ALI\Processors\PreProcessors;

/**
 * Class HtmlCommentPreProcessor
 * @package ALI\Processors
 */
class HtmlCommentPreProcessor extends PreProcessorAbstract
{
    /**
     * @param string $content
     * @return string
     */
    public function process($content)
    {
        return preg_replace('#(<!--.*-->)#Us', '', $content);
    }
}
