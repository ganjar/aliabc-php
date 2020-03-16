<?php

namespace ALI\Translate\Translators;

use ALI\Translate\Language\LanguageInterface;
use ALI\Translate\PhrasePackets\TranslatePhrasePacket;

/**
 * TranslatorInterface
 */
interface TranslatorInterface
{
    /**
     * @param array $originalPhrases
     * @return TranslatePhrasePacket
     */
    public function translateAll($originalPhrases);

    /**
     * @param string $originalPhrase
     * @return string
     */
    public function translate($originalPhrase);

    /**
     * @return bool
     */
    public function isCurrentLanguageOriginal();

    /**
     * @return LanguageInterface
     */
    public function getLanguage();
}
