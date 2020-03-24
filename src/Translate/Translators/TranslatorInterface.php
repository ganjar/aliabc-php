<?php

namespace ALI\Translate\Translators;

use ALI\Translate\Language\LanguageInterface;
use ALI\Translate\PhrasePackets\TranslatePhrasePacket;
use ALI\Translate\Sources\SourceInterface;

/**
 * TranslatorInterface
 */
interface TranslatorInterface
{
    /**
     * @param array $phrases
     * @return TranslatePhrasePacket
     */
    public function translateAll($phrases);

    /**
     * @param string $phrase
     * @return string
     */
    public function translate($phrase);

    /**
     * @return bool
     */
    public function isCurrentLanguageOriginal();

    /**
     * @return LanguageInterface
     */
    public function getLanguage();

    /**
     * @return SourceInterface
     */
    public function getSource();
}
