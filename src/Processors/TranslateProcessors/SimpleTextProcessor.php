<?php

namespace ALI\Processors\TranslateProcessors;

use ALI\Translate\Translators\TranslatorInterface;

/**
 * SimpleTextProcessor
 *
 * Uses for:
 * - translate simple buffer text, without html
 * - text buffering, for later translate all phrases by one request
 */
class SimpleTextProcessor implements TranslateProcessors
{
    /**
     * Symbols which disables this processor, when their exist in content
     *
     * @var array
     */
    protected $stopCharacters = [];

    /**
     * @param array $stopCharacters
     */
    public function __construct(array $stopCharacters = ['<'])
    {
        $this->stopCharacters = $stopCharacters;
    }

    /**
     * @param string $content
     * @param string $cleanContent
     * @param TranslatorInterface $translator
     * @return string|void
     */
    public function process($content, $cleanContent, TranslatorInterface $translator)
    {
        if ($this->stopCharacters) {
            foreach ($this->stopCharacters as $stopCharacter) {
                if (strpos($cleanContent, $stopCharacter) !== false) {
                    return $content;
                }
            }
        }

        return $translator->translateAll([$content])->getTranslate($content) ?: $content;
    }
}
