<?php

namespace ALI;

use ALI\Buffer\Buffer;
use ALI\Buffer\BufferCaptcher;
use ALI\Buffer\BufferContent;
use ALI\Buffer\BufferTranslate;
use ALI\Buffer\KeyGenerators\KeyGenerator;
use ALI\Buffer\KeyGenerators\StaticKeyGenerator;
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
     * @var KeyGenerator
     */
    protected $templatesKeyGenerator;

    /**
     * @var BufferTranslate
     */
    protected $bufferTranslate;

    /**
     * @param TranslatorInterface $translator
     * @param ProcessorsManager|null $processorsManager
     */
    public function __construct(TranslatorInterface $translator, ProcessorsManager $processorsManager = null)
    {
        $this->translator = $translator;
        $this->processorsManager = $processorsManager;
        $this->bufferCaptcher = new BufferCaptcher();
        $this->templatesKeyGenerator = new StaticKeyGenerator('{', '}');
        $this->bufferTranslate = new BufferTranslate();
    }

    /**
     * @param string $phrase
     * @param array $params
     * @return string
     */
    public function translate($phrase, array $params = [])
    {
        if (!$params) {
            return $this->translator->translate($phrase);
        }
        $bufferContent = $this->generateBufferContentByTemplate($phrase, $params);
        $buffer = new Buffer($this->templatesKeyGenerator);
        $layoutBufferContent = new BufferContent($buffer->add($bufferContent), $buffer);

        return $this->bufferTranslate->translateBuffer($layoutBufferContent, $this->translator);
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
     * @param $original
     */
    public function deleteOriginal($original)
    {
        $this->translator->getSource()->delete($original);
    }

    /**
     * @param $content
     * @param array $params
     * @return string
     */
    public function addToBuffer($content, array $params = [])
    {
        if (!$params) {
            return $this->bufferCaptcher->add($content);
        }

        $bufferContent = $this->generateBufferContentByTemplate($content, $params);

        return $this->bufferCaptcher->getBuffer()->add($bufferContent);
    }

    /**
     * @param $contentContext
     * @return string
     */
    public function translateBuffer($contentContext)
    {
        $buffer = $this->bufferCaptcher->getBuffer();
        $bufferContent = new BufferContent($contentContext, $buffer);

        if (!$this->processorsManager) {
            return $this->bufferTranslate->translateBuffer($bufferContent, $this->translator);
        }

        if ($this->isSourceSensitiveForRequestsCount($this->translator->getSource())) {
            return $this->bufferTranslate->translateBuffersWithProcessorsByOneRequest($bufferContent, $this->translator, $this->processorsManager);
        } else {
            return $this->bufferTranslate->translateBuffersWithProcessors($bufferContent, $this->translator, $this->processorsManager);
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

    /**
     * @param $phrase
     * @param array $contentParams
     * @return BufferContent
     */
    protected function generateBufferContentByTemplate($phrase, array $contentParams = [])
    {
        $buffer = new Buffer($this->templatesKeyGenerator);

        foreach ($contentParams as $bufferId => $bufferValue) {
            $buffer->add(new BufferContent($bufferValue), $bufferId);
        }

        return new BufferContent($phrase, $buffer);
    }
}
