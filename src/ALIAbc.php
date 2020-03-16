<?php

namespace ALI;

use ALI\Buffer\BufferTranslate;
use ALI\Exceptions\BufferTranslateNotDefinedException;
use ALI\Exceptions\TranslateNotDefinedException;
use ALI\Translate\Language\LanguageInterface;
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
     * @var BufferTranslate
     */
    protected $bufferTranslate;

    /**
     * @param BufferTranslate $bufferTranslate
     * @return $this
     */
    public function setBufferTranslate(BufferTranslate $bufferTranslate)
    {
        $this->bufferTranslate = $bufferTranslate;

        return $this;
    }

    /**
     * @return BufferTranslate
     * @throws BufferTranslateNotDefinedException
     */
    public function getBufferTranslate()
    {
        if (!$this->bufferTranslate) {
            throw new BufferTranslateNotDefinedException('BufferTranslate is not defined');
        }

        return $this->bufferTranslate;
    }

    /**
     * @param TranslatorInterface $translator
     * @return $this
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * @return TranslatorInterface
     * @throws TranslateNotDefinedException
     */
    public function getTranslator()
    {
        if (!$this->translator) {
            throw new TranslateNotDefinedException('Translate is not defined');
        }

        return $this->translator;
    }

    /**
     * @return LanguageInterface
     * @throws TranslateNotDefinedException
     */
    public function getLanguage()
    {
        return $this->getTranslator()->getLanguage();
    }
}
