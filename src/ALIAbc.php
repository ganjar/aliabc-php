<?php

namespace ALI;

use ALI\Buffer\BufferCaptcher;
use ALI\Buffer\BufferContent;
use ALI\Buffer\BufferTranslate;
use ALI\Exceptions\TranslateNotDefinedException;
use ALI\Processors\ProcessorsManager;
use ALI\Translate\Language\LanguageInterface;
use ALI\Translate\Sources\MySqlSource;
use ALI\Translate\Sources\SourceInterface;
use ALI\Translate\Translators\TranslatorInterface;

/**
 * Class ALI
 * @package ALI
 */
class ALIAbc
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var null|ProcessorsManager
     */
    protected $processorsManager;

    /**
     * @var BufferCaptcher
     */
    protected $bufferCaptcher;

    /**
     * @param TranslatorInterface $translator
     * @param ProcessorsManager|null $processorsManager
     */
    public function __construct(TranslatorInterface $translator, ProcessorsManager $processorsManager = null)
    {
        $this->translator = $translator;
        $this->processorsManager = $processorsManager;
        $this->bufferCaptcher = new BufferCaptcher();
    }

    /**
     * @param string $originalPhrase
     * @return string
     */
    public function translate($originalPhrase)
    {
        return $this->translator->translate($originalPhrase);
    }

    /**
     * @param array $originalPhrases
     * @return Translate\PhrasePackets\TranslatePhrasePacket
     */
    public function translateAll($originalPhrases)
    {
        return $this->translator->translateAll($originalPhrases);
    }

    /**
     * @param string $original
     * @param string $translate
     * @throws Translate\Sources\Exceptions\SourceException
     */
    public function saveTranslate($original, $translate)
    {
        $currentLanguage = $this->translator->getLanguage();
        $this->translator->getSource()->saveTranslate($currentLanguage, $original, $translate);
    }

    /**
     * @param $content
     * @return string
     */
    public function addToBuffer($content)
    {
        return $this->bufferCaptcher->add($content);
    }

    /**
     * @param $contentContext
     * @return string
     */
    public function translateBuffer($contentContext)
    {
        $buffer = $this->bufferCaptcher->getBuffer();
        $bufferContent = new BufferContent($contentContext, $buffer);
        $bufferTranslate = new BufferTranslate();

        if ($this->processorsManager) {
            return $bufferTranslate->translateBuffer($bufferContent, $this->translator);
        }

        if ($this->isSourceSensitiveForRequestsCount($this->translator->getSource())) {
            return $bufferTranslate->translateBuffersWithProcessorsByOneRequest($bufferContent, $this->translator, $this->processorsManager);
        } else {
            return $bufferTranslate->translateBuffersWithProcessors($bufferContent, $this->translator, $this->processorsManager);
        }
    }

    /**
     * @param SourceInterface $source
     * @return bool
     */
    protected function isSourceSensitiveForRequestsCount(SourceInterface $source)
    {
        switch (get_class($source)) {
            case MySqlSource::class:
                return true;
                break;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isCurrentLanguageOriginal()
    {
        return $this->translator->isCurrentLanguageOriginal();
    }

    /**
     * @return LanguageInterface
     * @throws TranslateNotDefinedException
     */
    public function getLanguage()
    {
        return $this->translator->getLanguage();
    }

    /**
     * @return BufferCaptcher
     */
    public function getBufferCaptcher()
    {
        return $this->bufferCaptcher;
    }
}
