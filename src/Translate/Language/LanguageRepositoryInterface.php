<?php

namespace ALI\Translate\Language;

/**
 * LanguageRepositoryInterface
 */
interface LanguageRepositoryInterface
{
    /**
     * @param string $alias
     * @return null|Language
     */
    public function findLanguage($alias);

    /**
     * @return Language[]
     */
    public function getAllLanguages();
}
