<?php

namespace ALI\Buffer\KeyGenerators;

/**
 * BufferKeyGenerator Interface
 */
interface KeyGenerator
{
    /**
     * @param string $contentId
     * @return string
     */
    public function generateKey($contentId);
}
