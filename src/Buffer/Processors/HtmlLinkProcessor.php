<?php

namespace ALI\Buffer\Processors;

/**
 * You may use this processor if you want to store information about language in URL.
 * Processor replace all links to links with current language (/about/ -> /ru/about/)
 * You can use " % " before html attribute for skipping URL replacing (<a % href="/test">test</a>)
 * Class HtmlLinkProcessor
 * @package ALI\Buffer\Processors
 */
class HtmlLinkProcessor extends ProcessorAbstract
{
    /**
     * Usually $_SERVER['HTTP_HOST']
     * @var string
     */
    protected $currentHttpHost;

    /**
     * Attributes where we can find URLs ('href', 'action', 'src')
     * @var array
     */
    protected $attributesWithLinks = [];

    /**
     * Allowed to change URLs with file extensions ('html', 'htm', 'php')
     * @var array
     */
    protected $allowedExtensions = [];

    /**
     * HtmlLinkProcessor constructor.
     * @param string $currentHttpHost
     * @param array  $attributesWithLinks
     * @param array  $allowedExtensions
     */
    public function __construct(
        $currentHttpHost,
        array $attributesWithLinks = ['href', 'action'],
        array $allowedExtensions = ['html', 'php']
    ) {
        $this->currentHttpHost = $currentHttpHost;
        $this->attributesWithLinks = $attributesWithLinks;
        $this->allowedExtensions = $allowedExtensions;
    }

    /**
     * @return string
     */
    public function getCurrentHttpHost()
    {
        return $this->currentHttpHost;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    /**
     * @return array
     */
    public function getAttributesWithLinks()
    {
        return $this->attributesWithLinks;
    }

    /**
     * @param string $buffer
     * @param string $cleanBuffer
     * @return string
     * @throws \ALI\Exceptions\ALIException
     */
    public function process($buffer, $cleanBuffer)
    {
        if ($this->getTranslate()->getLanguage()->getIsOriginal()) {
            $buffer = $this->removeExceptionMark($buffer);
            return $buffer;
        }

        $attributesRegex = [];
        $attributes = $this->getAttributesWithLinks();
        foreach ($attributes as $attribute) {
            $attributesRegex[] = '(?:' . preg_quote($attribute) . ')';
        }

        $buffer = preg_replace_callback(
            '$  
                (?<start><\w+       #Html tag
                    (?!\s\%\s)      #Skip exceptions
                    (?:[^>]*)\s     #Any attributes
                    (?:' . implode('|', $attributesRegex) . ')
                        =
                    ("|\')
                )
                    (?<url>.+)
                (?<end>
                    (?!\\\)\\2
                )
                $Usix',
            function ($matches) {
                return $matches['start'] . $this->getLocalizedUrl($matches['url']) . $matches['end'];
            },
            $buffer
        );

        $buffer = $this->removeExceptionMark($buffer);

        return $buffer;
    }

    /**
     * Get localized URL (only URL starts from /, //, https://, http://)
     * @var string
     * @return string
     * @throws \ALI\Exceptions\ALIException
     */
    public function getLocalizedUrl($url)
    {
        $language = $this->getTranslate()->getLanguage();

        if ($language->getIsOriginal()) {
            return $url;
        }

        if (!$this->isUrlHasAllowedExtension($url)) {
            return $url;
        }

        $languageAlias = $language->getAlias();

        return preg_replace(
            '#^
                (?:
                    (?:(?:https?:)?//' . preg_quote($this->getCurrentHttpHost()) . '/)
                    |
                    (?:/(?!/))
                )
                (?!' . preg_quote($languageAlias) . '(?:/|\Z))#Uixs',
            '$0' . $languageAlias . '/', $url
        );
    }

    /**
     * @param string $buffer
     * @return string
     */
    protected function removeExceptionMark($buffer)
    {
        $buffer = preg_replace('#(<\w+)\s%\s#', '$1 ', $buffer);

        return $buffer;
    }

    /**
     * @param $url
     * @return bool
     */
    protected function isUrlHasAllowedExtension($url)
    {
        $isAllowed = true;

        $urlParts = explode('.', $url);
        if (isset($urlParts[1])) {
            $extension = $urlParts[count($urlParts) - 1];

            $isAllowed = in_array(strtolower($extension), $this->getAllowedExtensions(), true);
        }

        return $isAllowed;
    }
}