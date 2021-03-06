<?php

namespace ALI\Buffer\PreProcessors;

/**
 * Class ClearHtmlPreProcessor
 * @package ALI\Processors
 */
class IgnoreHtmlTagsPreProcessor extends PreProcessorAbstract
{
    /**
     * @var array
     */
    protected $ignoreTags = [];

    /**
     * IgnoreHtmlTagsPreProcessor constructor.
     * @param array $ignoreTags
     */
    public function __construct(array $ignoreTags = ['script', 'style'])
    {
        $this->ignoreTags = $ignoreTags;
    }

    /**
     * Disallow HTML tags translation
     * @return array
     */
    public function getIgnoreTags()
    {
        return $this->ignoreTags;
    }

    /**
     * @param array $ignoreTags
     * @return $this
     */
    public function setIgnoreTags($ignoreTags)
    {
        $this->ignoreTags = $ignoreTags;

        return $this;
    }

    /**
     * @param string $content
     * @return string
     */
    public function process($content)
    {
        $regexp = [];

        $ignoreTags = $this->getIgnoreTags();
        foreach ($ignoreTags as $tag) {
            $tag = preg_quote($tag);
            $regexp[] = '(<' . $tag . '[\s>].*</' . $tag . '>)';
        }

        $content = preg_replace('#' . implode('|', $regexp) . '#Usi', '', $content);

        return $content;
    }
}