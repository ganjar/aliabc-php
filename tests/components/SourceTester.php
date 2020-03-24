<?php

namespace ALI\Tests\components;

use ALI\Translate\Language\Language;
use ALI\Translate\Sources\Exceptions\SourceException;
use ALI\Translate\Sources\SourceInterface;
use PHPUnit\Framework\TestCase;

/**
 * SourceTester
 */
class SourceTester
{
    /**
     * @param SourceInterface $source
     * @param TestCase $testCase
     * @throws SourceException
     */
    public function testSource(SourceInterface $source, TestCase $testCase)
    {
        $languageForTranslate = new Language('ua', 'Ukraine');

        $originalPhrase = 'Hello';
        $translatePhrase = 'Привіт';

        // Test adding new translate
        $source->saveTranslate($languageForTranslate, $originalPhrase, $translatePhrase);
        $translatePhraseFromSource = $source->getTranslate($originalPhrase, $languageForTranslate);

        $testCase->assertEquals($translatePhrase, $translatePhraseFromSource);

        // Test removing translate
        $source->delete($originalPhrase);
        $translatePhraseFromSource = $source->getTranslate($originalPhrase, $languageForTranslate);

        $testCase->assertEquals('', $translatePhraseFromSource);
    }
}
