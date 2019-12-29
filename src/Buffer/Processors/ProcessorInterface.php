<?php

namespace ALI\Buffer\Processors;

use ALI\Translate\Translate;

/**
 * Interface ProcessorInterface
 * @package ALI\Buffer\Processors
 */
interface ProcessorInterface
{
    /**
     * @param Translate $translate
     * @return $this
     */
    public function setTranslate(Translate $translate);

    /**
     * @return Translate
     */
    public function getTranslate();

    /**
     * @param string $buffer
     * @param string $cleanBuffer
     * @return string
     */
    public function process($buffer, $cleanBuffer);
}