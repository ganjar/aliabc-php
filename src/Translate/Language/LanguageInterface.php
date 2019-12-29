<?php

namespace ALI\Translate\Language;

interface LanguageInterface
{
    /**
     * Language title (Русский, English)
     * @return string
     */
    function getTitle();

    /**
     * Language alias (ru, en)
     * @return string
     */
    function getAlias();

    /**
     * Get is language occur original language for content.
     * In this case - we does not need translation and always return original content.
     * @return bool
     */
    function getIsOriginal();
}