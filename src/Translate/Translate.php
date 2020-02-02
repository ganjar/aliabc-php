<?php


namespace ALI\Translate;


use ALI\Exceptions\ALIException;
use ALI\Translate\Language\LanguageInterface;
use ALI\Translate\OriginalProcessors\OriginalProcessorInterface;
use ALI\Translate\Sources\SourceInterface;
use ALI\Translate\TranslateProcessors\TranslateProcessorInterface;
use function is_callable;

/**
 * Class Translate
 * @package ALI
 */
class Translate
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
     * @param array         $phrases
     * @return array
     */
    public function translateAll(array $phrases)
    {
        $translatesResult = [];
        $searchPhrases = $originalPhrases = [];
        foreach ($phrases as $phrase) {
            if (!$phrase) {
                continue;
            }
            $searchPhrases[$phrase] = $this->originalProcess($phrase);
            $originalPhrases[$searchPhrases[$phrase]] = $phrase;
        }

        $translatesFromSource = $this->getSource()->getTranslates(
            $searchPhrases,
            $this->getLanguage()
        );

        foreach ($searchPhrases as $originalPhrase => $searchPhrase) {
            $translate = isset($translatesFromSource[$searchPhrase]) ? $translatesFromSource[$searchPhrase] : '';
            if ($translate === '') {
                if (is_callable($this->getMissingTranslationCallback())) {
                    $translate = $this->getMissingTranslationCallback()($searchPhrase, $this) ?: '';
                }
            }

            if ($translate !== '') {
                $translate = $this->translateProcess($originalPhrase, $translate);
            }

            $translatesResult[$originalPhrase] = $translate;
        }

        return $translatesResult;
    }

    /**
     * Fast translate without buffers and processors
     * @param string $phrase
     * @return string
     * @throws ALIException
     */
    public function translate($phrase)
    {
        foreach ($this->translateAll([$phrase]) as $translate) {
            return $translate;
        }

        throw new ALIException('Empty list of translated phrases');
    }

    /**
     * @param LanguageInterface $language
     * @param string            $original
     * @param string            $translate
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