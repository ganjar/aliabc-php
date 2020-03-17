<?php

namespace ALI\Buffer;

use ALI\Buffer\KeyGenerators\StaticKeyGenerator;
use ALI\Processors\ProcessorsManager;
use ALI\Translate\PhrasePackets\OriginalPhrasePacket;
use ALI\Translate\PhrasePackets\TranslatePhrasePacket;
use ALI\Translate\Translators\BufferTranslator;
use ALI\Translate\Translators\Translator;
use ALI\Translate\Translators\TranslatorInterface;

/**
 * Class BufferTranslate
 * @package ALI
 */
class BufferTranslate
{
    /**
     * Translates all buffer contents, and replace their in parent content
     *
     * @param BufferContent $bufferContent
     * @param Translator $translator
     * @return string
     */
    public function translateBuffer(BufferContent $bufferContent, Translator $translator)
    {
        if (!$bufferContent->getBuffer()) {
            return $bufferContent->getContentString();
        }

        $originalsPacket = new OriginalPhrasePacket();
        foreach ($bufferContent->getBuffer()->getBuffersContent() as $childBufferContent) {
            $originalsPacket->add($childBufferContent->getContentString());
        }
        $translatedPacket = $translator->translateAll($originalsPacket->getAll());

        return $this->replaceBufferByTranslatedPacket($bufferContent, $translatedPacket);
    }

    /**
     * Replace content by buffers translation. Which pieces buffer content will be translated detecting processorsManager.
     * Method makes N requests to translator source, where N = (buffers count * active processors)
     * if for your source are sensitive for request count - use method "translateContentByOneRequest"
     * @see translateBuffersWithProcessorsByOneRequest
     *
     * @param BufferContent $bufferContent
     * @param TranslatorInterface $translator
     * @return string
     */
    public function translateBuffersWithProcessors(BufferContent $bufferContent, TranslatorInterface $translator, ProcessorsManager $processorsManager)
    {
        $buffer = $bufferContent->getBuffer();
        if (!$buffer) {
            return $bufferContent->getContentString();
        }
        $content = $bufferContent->getContentString();

        foreach ($buffer->getBuffersContent() as $bufferId => $childBufferContent) {
            $bufferKey = $buffer->generateBufferKey($bufferId);

            // resolve child buffers
            $translatedSting = $processorsManager->executeProcesses($childBufferContent->getContentString(), $translator);
            if ($childBufferContent->getBuffer()) {
                $translatedSting = $this->translateBuffersWithProcessors(new BufferContent($translatedSting, $childBufferContent->getBuffer()), $translator, $processorsManager);
            }
            $content = str_replace(
                $bufferKey,
                $translatedSting,
                $content
            );
        }

        return $content;
    }

    /**
     * Optimization for method "translateBuffersWithProcessors"
     * @see translateBuffersWithProcessors     *
     *
     * If you has many buffers, and source sensitive for request count,
     * this method may decrease request numbers to one.
     * But this method create more php actions with content replacing
     *
     * @param BufferContent $bufferContent
     * @param Translator $translator
     * @return string
     */
    public function translateBuffersWithProcessorsByOneRequest(BufferContent $bufferContent, Translator $translator, ProcessorsManager $processorsManager)
    {
        // Init additional objects
        $bufferLayer = new Buffer(new StaticKeyGenerator('#ali-buffer-layer-content_', '#'));
        $bufferLayerTranslator = new BufferTranslator(
            $translator->getLanguage(),
            $translator->getSource()->getOriginalLanguage(),
            $bufferLayer
        );

        // Create additional buffering layer
        $layerContent = $this->translateBuffersWithProcessors($bufferContent, $bufferLayerTranslator, $processorsManager);

        return $this->translateBuffer(new BufferContent($layerContent, $bufferLayer), $translator);
    }

    /**
     * @param BufferContent $bufferContent
     * @param TranslatePhrasePacket $translatePhrasePacket
     * @return string|string[]
     */
    private function replaceBufferByTranslatedPacket(BufferContent $bufferContent, TranslatePhrasePacket $translatePhrasePacket)
    {
        $buffer = $bufferContent->getBuffer();
        $content = $bufferContent->getContentString();
        foreach ($buffer->getBuffersContent() as $bufferId => $bufferContent) {
            $bufferKey = $buffer->generateBufferKey($bufferId);
            $translatedSting = $translatePhrasePacket->getTranslate($bufferContent->getContentString()) ?: $bufferContent->getContentString();
            $content = str_replace(
                $bufferKey,
                $translatedSting,
                $content
            );
        }

        return $content;
    }
}
