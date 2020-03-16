<?php

namespace ALI\Buffer;

use ALI\Processors\ProcessorsManager;
use ALI\Translate\Translators\Translator;
use ALI\Translate\Translators\TranslatorInterface;

/**
 * Class BufferTranslate
 * @package ALI
 */
class BufferTranslate
{
    /**
     * @var ProcessorsManager
     */
    protected $processorsManager;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @param ProcessorsManager $processorsManager
     * @param Translator $translator
     */
    public function __construct(ProcessorsManager $processorsManager, TranslatorInterface $translator)
    {
        $this->processorsManager = $processorsManager;
        $this->translator = $translator;
    }

    /**
     * Process all buffers and clear stack
     * @param BufferContent $bufferContent
     * @return string
     */
    public function translateContent(BufferContent $bufferContent)
    {
        $buffer = $bufferContent->getBuffer();
        if (!$buffer) {
            return $bufferContent->getContentString();
        }
        $content = $bufferContent->getContentString();

        foreach ($buffer->getBuffersContent() as $bufferId => $childBufferContent) {
            $bufferKey = $buffer->generateBufferKey($bufferId);

            // resolve child buffers
            $childBufferContentSting = $this->translateContent($childBufferContent);
            $translatedSting = $this->processorsManager->executeProcesses($childBufferContentSting, $this->translator);
            $content = str_replace(
                $bufferKey,
                $translatedSting,
                $content
            );
            $buffer->remove($bufferId);
        }
        $buffer->clear();

        return $content;
    }
}
