<?php

namespace ALI\Tests\unit;

use ALI\Helpers\QuickStart\ALIAbFactory;
use ALI\Tests\components\Factories\LanguageFactory;
use ALI\Translate\Sources\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translate\Sources\Exceptions\SourceException;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class ALIAbcTest extends TestCase
{
    /**
     * @throws UnsupportedLanguageAliasException
     * @throws SourceException
     */
    public function testTemplateWithParams()
    {
        $aliAbc = (new ALIAbFactory())->createALIByCsvSource(SOURCE_CSV_PATH, LanguageFactory::ORIGINAL_LANGUAGE_ALIAS, LanguageFactory::CURRENT_LANGUAGE_ALIAS);
        $aliAbc->saveTranslate('Hello {objectName}!', 'Привіт {objectName}!');
        $aliAbc->saveTranslate('sun', 'сонце');

        $translated = $aliAbc->translate('Hello {objectName}!', [
            'objectName' => 'sun',
        ]);
        $this->assertEquals('Привіт сонце!', $translated);

        $content = '<div>'. $aliAbc->addToBuffer('Hello {objectName}!', [
                'objectName' => 'sun',
            ]) .'</div>';
        $translated = $aliAbc->translateBuffer($content);
        $this->assertEquals('<div>Привіт сонце!</div>', $translated);

        $aliAbc->deleteOriginal('Hello {objectName}!');;
        $aliAbc->deleteOriginal('sun');;
    }
}
