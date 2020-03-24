<?php

namespace ALI\Processors\TranslateProcessors;

use ALI\Exceptions\ALIException;
use ALI\Translate\Translators\TranslatorInterface;
use function preg_quote;
use function preg_replace;

/**
 * Class CustomTagProcessor
 * @package ALI\Processors\TranslateProcessors
 */
class CustomTagProcessor extends AbstractHtmlProcessor
{
    /**
     * @var string
     */
    protected $openTag;

    /**
     * @var string
     */
    protected $closeTag;

    /**
     * @var bool
     */
    protected $removeOpenCloseTags;

    /**
     * CustomTagProcessor constructor.
     * @param string $openTag
     * @param string $closeTag
     * @param bool   $removeOpenCloseTags
     */
    public function __construct($openTag, $closeTag, $removeOpenCloseTags = true)
    {
        $this->openTag = $openTag;
        $this->closeTag = $closeTag;
        $this->removeOpenCloseTags = $removeOpenCloseTags;
    }

    /**
     * Get RegEx for parse HTML and get all phrases for translate
     * @return string
     */
    public function getFindPhrasesRegex()
    {
        return '$' . preg_quote($this->openTag) . '(?<original>.+)' . preg_quote($this->closeTag) . '$Usi';
    }

    /**
     * @param string $content
     * @param string $cleanContent
     * @param TranslatorInterface $translator
     * @return string
     * @throws ALIException
     */
    public function process($content, $cleanContent,TranslatorInterface $translator)
    {
        $content = parent::process($content, $cleanContent, $translator);

        if ($this->removeOpenCloseTags) {
            $content = preg_replace($this->getFindPhrasesRegex(), '$1', $content);
        }

        return $content;
    }
}
