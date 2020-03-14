<?php

namespace ALI\Translate\Language;

/**
 * Language
 */
class Language implements LanguageInterface
{
    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $title;

    /**
     * Language constructor.
     * @param string $alias
     * @param string $title
     * @param bool   $isOriginal
     */
    public function __construct($alias, $title = '', $isOriginal = false)
    {
        $this->alias = $alias;
        $this->title = $title;
        $this->isOriginal = $isOriginal;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
