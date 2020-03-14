<?php

namespace ALI\Buffer;

use ALI\Buffer\PreProcessors\PreProcessorInterface;
use ALI\Buffer\Processors\ProcessorInterface;
use ALI\Exceptions\TranslateNotDefinedException;
use ALI\Translate\Translate;

/**
 * Class BufferTranslate
 * @package ALI
 */
class BufferTranslate
{
    /**
     * @var Translate
     */
    protected $translate;

    /**
     * @var Buffer
     */
    protected $buffer;

    /**
     * @var PreProcessorInterface[]
     */
    protected $preProcessors = [];

    /**
     * @var ProcessorInterface[]
     */
    protected $processors = [];

    /**
     * BufferTranslate constructor.
     * @param Translate $translate
     */
    public function __construct(Translate $translate)
    {
        $this->translate = $translate;
    }

    /**
     * @return ProcessorInterface[]
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * @param ProcessorInterface[] $processors
     * @return $this
     */
    public function setProcessors($processors)
    {
        $this->processors = $processors;

        return $this;
    }

    /**
     * Add translate processor
     * @param ProcessorInterface $processor
     * @throws TranslateNotDefinedException
     */
    public function addProcessor(ProcessorInterface $processor)
    {
        $processor->setTranslate($this->getTranslate());
        $this->processors[] = $processor;
    }

    /**
     * @return PreProcessorInterface[]
     */
    public function getPreProcessors()
    {
        return $this->preProcessors;
    }

    /**
     * @param PreProcessorInterface[] $preProcessors
     * @return $this
     */
    public function setPreProcessors($preProcessors)
    {
        $this->preProcessors = $preProcessors;

        return $this;
    }

    /**
     * Add translate processor
     * @param PreProcessorInterface $preProcessor
     */
    public function addPreProcessor(PreProcessorInterface $preProcessor)
    {
        $this->preProcessors[] = $preProcessor;
    }

    /**
     * @return Translate
     * @throws TranslateNotDefinedException
     */
    public function getTranslate()
    {
        if (is_null($this->translate)) {
            throw new TranslateNotDefinedException('Translate object must be defined');
        }

        return $this->translate;
    }

    /**
     * @return Buffer
     */
    public function getBuffer()
    {
        if (is_null($this->buffer)) {
            $this->setBuffer(new Buffer());
        }

        return $this->buffer;
    }

    /**
     * @param Buffer $buffer
     * @return $this
     */
    public function setBuffer(Buffer $buffer)
    {
        $this->buffer = $buffer;

        return $this;
    }

    /**
     * Process all buffers and clear stack
     * @param $content
     * @return mixed
     */
    public function translateAllAndReplaceInSource($content)
    {
        //The maximum number of iterations to find the buffer identifier in other buffers
        $maxIterations = count($this->getBuffer()->getBuffersContent());

        for ($i = 0; $i < $maxIterations; $i++) {
            $buffersContent = $this->getBuffer()->getBuffersContent();

            $findSuccess = false;
            foreach ($buffersContent as $bufferId => $bufferContent) {
                $bufferKey = $this->getBuffer()->getBufferKey($bufferId);
                if (strpos($content, $bufferKey) !== false) {
                    $translateBufferContent = $this->translateContent($bufferContent);
                    $content = str_replace(
                        $bufferKey,
                        $translateBufferContent,
                        $content
                    );
                    $this->getBuffer()->remove($bufferId);
                    //Decrease max iterations if we found buffer id in content
                    $maxIterations--;
                    $findSuccess = true;
                }
            }
            //Break if iteration without result
            if (!$findSuccess) {
                break;
            }
        }

        $this->getBuffer()->clear();

        return $content;
    }

    /**
     * Run all processors by content
     * @param string $content
     * @return string
     */
    public function translateContent($content)
    {
        $cleanBuffer = $content;
        foreach ($this->getPreProcessors() as $preProcessor) {
            $cleanBuffer = $preProcessor->process($cleanBuffer);
        }

        foreach ($this->getProcessors() as $processor) {
            $content = $processor->process($content, $cleanBuffer);
        }

        return $content;
    }

    /**
     * This method starts global buffering
     * for translate all buffers in source
     */
    public function initBuffering()
    {
        ob_start(function ($buffer) {
            return $this->translateAllAndReplaceInSource($buffer);
        });
    }
}
