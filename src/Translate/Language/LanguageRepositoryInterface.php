<?php

namespace ALI\Translate\Language;

/**
 * LanguageRepositoryInterface
 */
interface LanguageRepositoryInterface
{
    /**
     * @param Language $language
     * @param bool $isActive
     * @return mixed
     */
    public function save(Language $language, $isActive);

    /**
     * @param string $alias
     * @return null|Language
     */
    public function find($alias);

    /**
     * @param bool $onlyActive
     * @return Language[]|array
     */
    public function getAll($onlyActive);
}
