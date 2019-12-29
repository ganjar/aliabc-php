<?php

namespace ALI\Buffer\Processors;

use ALI\Exceptions\ALIException;
use ALI\Translate\Translate;

/**
 * Interface ProcessorInterface
 * @package ALI\Buffer\Processors
 */
abstract class ProcessorAbstract implements ProcessorInterface
{
    /**
     * @var Translate
     */
    protected $translate;

    /**
     * @return Translate
     * @throws ALIException
     */
    public function getTranslate()
    {
        if (!$this->translate) {
            throw new ALIException('Uninitialized Translate object');
        }
        return $this->translate;
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
}