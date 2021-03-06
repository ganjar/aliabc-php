<?php

namespace ALI;

use ALI\Buffer\Buffer;
use ALI\Buffer\BufferTranslate;
use ALI\Exceptions\BufferTranslateNotDefinedException;
use ALI\Exceptions\TranslateNotDefinedException;
use ALI\Translate\Language\LanguageInterface;
use ALI\Translate\Translate;

/**
 * Class ALI
 * @package ALI
 */
class ALIAbc
{
    /**
     * @var Translate
     */
    protected $translate;

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
     * @return Buffer
     * @throws BufferTranslateNotDefinedException
     */
    public function getBuffer()
    {
        return $this->getBufferTranslate()->getBuffer();
    }

    /**
     * @param Translate $translate
     * @return $this
     */
    public function setTranslate(Translate $translate)
    {
        $this->translate = $translate;

        return $this;
    }

    /**
     * @return Translate
     * @throws TranslateNotDefinedException
     */
    public function getTranslate()
    {
        if (!$this->translate) {
            throw new TranslateNotDefinedException('Translate is not defined');
        }

        return $this->translate;
    }

    /**
     * @return LanguageInterface
     * @throws TranslateNotDefinedException
     */
    public function getLanguage()
    {
        return $this->getTranslate()->getLanguage();
    }

    /**
     * This method starts global buffering
     * for translate all buffers in source
     * @throws BufferTranslateNotDefinedException
     */
    public function initSourceBuffering()
    {
        $this->getBufferTranslate()->initBuffering();
    }
}