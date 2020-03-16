<?php

namespace ALI\Translate\Translators;

use ALI\Buffer\Buffer;
use ALI\Translate\Language\LanguageInterface;
use ALI\Translate\PhrasePackets\TranslatePhrasePacket;

/**
 * BufferTranslator
 */
class BufferTranslator implements TranslatorInterface
{
    /**
     * @var LanguageInterface
     */
    protected $language;

    /**
     * @var LanguageInterface
     */
    protected $originalLanguage;

    /**
     * @var Buffer
     */
    protected $buffer;

    /**
     * @param LanguageInterface $language
     * @param LanguageInterface $originalLanguage
     * @param Buffer $buffer
     */
    public function __construct(LanguageInterface $language, LanguageInterface $originalLanguage, Buffer $buffer)
    {
        $this->language = $language;
        $this->originalLanguage = $originalLanguage;
        $this->buffer = $buffer;
    }

    /**
     * @param array $originalPhrases
     * @return TranslatePhrasePacket
     */
    public function translateAll($originalPhrases)
    {
        $isCurrentLanguageOriginal = $this->isCurrentLanguageOriginal();

        $translatePacket = new TranslatePhrasePacket();
        foreach ($originalPhrases as $originalPhrase) {
            if (!$isCurrentLanguageOriginal) {
                $translate = $this->buffer->addContent($originalPhrase);
            } else {
                $translate = $originalPhrase;
            }

            $translatePacket->addTranslate($originalPhrase, $translate);
        }

        return $translatePacket;
    }

    /**
     * @param string $originalPhrase
     * @return string
     */
    public function translate($originalPhrase)
    {
        if ($this->isCurrentLanguageOriginal()) {
            return $originalPhrase;
        }

        return $this->buffer->addContent($originalPhrase);
    }

    /**
     * @return bool
     */
    public function isCurrentLanguageOriginal()
    {
        return $this->originalLanguage->getAlias() === $this->language->getAlias();
    }

    /**
     * @return Buffer
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * @return LanguageInterface
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
