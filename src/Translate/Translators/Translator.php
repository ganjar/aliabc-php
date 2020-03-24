<?php

namespace ALI\Translate\Translators;

use ALI\Translate\Language\LanguageInterface;
use ALI\Translate\OriginalProcessors\OriginalProcessorInterface;
use ALI\Translate\PhrasePackets\TranslatePhrasePacket;
use ALI\Translate\Sources\Exceptions\SourceException;
use ALI\Translate\Sources\SourceInterface;
use ALI\Translate\TranslateProcessors\TranslateProcessorInterface;
use function is_callable;

/**
 * Class Translate
 * @package ALI
 */
class Translator implements TranslatorInterface
{
    /**
     * @var LanguageInterface
     */
    protected $language;

    /**
     * @var SourceInterface
     */
    protected $source;

    /**
     * @var \Closure
     */
    protected $missingTranslationCallback;

    /**
     * @var OriginalProcessorInterface[]
     */
    protected $originalProcessors = [];

    /**
     * @var TranslateProcessorInterface[]
     */
    protected $translateProcessors = [];

    /**
     * Translate constructor.
     * @param LanguageInterface $language
     * @param SourceInterface   $source
     * @param \Closure|null     $missingTranslationCallback
     */
    public function __construct(LanguageInterface $language, SourceInterface $source, \Closure $missingTranslationCallback = null)
    {
        $this->language = $language;
        $this->source = $source;
        $this->missingTranslationCallback = $missingTranslationCallback;
    }

    /**
     * @return bool
     */
    public function isCurrentLanguageOriginal()
    {
        return $this->language->getAlias() === $this->source->getOriginalLanguage()->getAlias();
    }

    /**
     * @return LanguageInterface
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return SourceInterface
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return \Closure
     */
    public function getMissingTranslationCallback()
    {
        return $this->missingTranslationCallback;
    }

    /**
     * @param \Closure $missingTranslationCallback
     * @return $this
     */
    public function setMissingTranslationCallback($missingTranslationCallback)
    {
        $this->missingTranslationCallback = $missingTranslationCallback;

        return $this;
    }

    /**
     * @return OriginalProcessorInterface[]
     */
    public function getOriginalProcessors()
    {
        return $this->originalProcessors;
    }

    /**
     * @param OriginalProcessorInterface[] $originalProcessors
     * @return $this
     */
    public function setOriginalProcessors($originalProcessors)
    {
        $this->originalProcessors = $originalProcessors;

        return $this;
    }

    /**
     * @param OriginalProcessorInterface $originalProcessor
     */
    public function addOriginalProcessor(OriginalProcessorInterface $originalProcessor)
    {
        $this->originalProcessors[] = $originalProcessor;
    }

    /**
     * @return TranslateProcessorInterface[]
     */
    public function getTranslateProcessors()
    {
        return $this->translateProcessors;
    }

    /**
     * @param TranslateProcessorInterface[] $translateProcessors
     * @return $this
     */
    public function setTranslateProcessors($translateProcessors)
    {
        $this->translateProcessors = $translateProcessors;

        return $this;
    }

    /**
     * @param TranslateProcessorInterface $translateProcessor
     */
    public function addTranslateProcessor(TranslateProcessorInterface $translateProcessor)
    {
        $this->translateProcessors[] = $translateProcessor;
    }

    /**
     * @param array $phrases
     * @return TranslatePhrasePacket
     */
    public function translateAll($phrases)
    {
        $translatePhrasePacket = new TranslatePhrasePacket();

        if ($this->isCurrentLanguageOriginal()) {
            foreach ($phrases as $phrase) {
                $translatePhrasePacket->addTranslate($phrase, null);
            }

            return $translatePhrasePacket;
        }

        $searchPhrases = [];
        foreach ($phrases as $phrase) {
            if (!$phrase) {
                continue;
            }
            $searchPhrases[$phrase] = $this->originalProcess($phrase);
        }

        $translatesFromSource = $this->getSource()->getTranslates(
            $searchPhrases,
            $this->getLanguage()
        );

        foreach ($searchPhrases as $originalPhrase => $searchPhrase) {
            $translate = isset($translatesFromSource[$searchPhrase]) ? $translatesFromSource[$searchPhrase] : null;
            if ($translate === null) {
                if (is_callable($this->getMissingTranslationCallback())) {
                    $translate = $this->getMissingTranslationCallback()($searchPhrase, $this) ?: '';
                }
            }

            if ($translate !== null) {
                $translate = $this->translateProcess($originalPhrase, $translate);
            }

            $translatePhrasePacket->addTranslate($originalPhrase,$translate);
        }

        return $translatePhrasePacket;
    }

    /**
     * Fast translate without buffers and processors
     *
     * @param string $phrase
     * @return string|null
     */
    public function translate($phrase)
    {
        return $this->translateAll([$phrase])->getTranslate($phrase);
    }

    /**
     * @param LanguageInterface $language
     * @param $original
     * @param $translate
     * @throws SourceException
     */
    public function saveTranslate(LanguageInterface $language, $original, $translate)
    {
        $this->getSource()->saveTranslate(
            $language,
            $this->originalProcess($original),
            $translate
        );
    }

    /**
     * Delete original and all translated phrases
     * @param $original
     */
    public function delete($original)
    {
        $this->getSource()->delete(
            $this->originalProcess($original)
        );
    }

    /**
     * @param $original
     * @return string
     */
    protected function originalProcess($original)
    {
        foreach ($this->getOriginalProcessors() as $originalProcessor) {
            $original = $originalProcessor->process($original);
        }

        return $original;
    }

    /**
     * @param string $original
     * @param string $translate
     * @return string
     */
    protected function translateProcess($original, $translate)
    {
        foreach ($this->getTranslateProcessors() as $translateProcessor) {
            $translate = $translateProcessor->process($original, $translate);
        }

        return $translate;
    }
}
