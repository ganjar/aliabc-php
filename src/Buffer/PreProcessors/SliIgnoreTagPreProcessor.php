<?php

namespace ALI\Buffer\PreProcessors;

/**
 * Class SliIgnoreTagPreProcessor
 * @package ALI\Processors
 */
class SliIgnoreTagPreProcessor extends PreProcessorAbstract
{
    const HTML_VAR_PATTERN = '<!--ALI::%s-->';

    /**
     * @param string $content
     * @return string
     */
    public function process($content)
    {
        return preg_replace('#(' . $this->ignoreStart() . '.*' . $this->ignoreEnd() . ')#Usi', '', $content);
    }

    /**
     * @return string
     * @internal param bool $print
     */
    public function ignoreStart()
    {
        return sprintf(self::HTML_VAR_PATTERN, 'ignore');
    }

    /**
     * @return string
     */
    public function ignoreEnd()
    {
        return sprintf(self::HTML_VAR_PATTERN, 'endIgnore');
    }
}