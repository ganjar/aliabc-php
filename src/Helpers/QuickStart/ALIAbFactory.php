<?php

namespace ALI\Helpers\QuickStart;

use ALI\ALIAbc;
use ALI\Processors\PreProcessors\HtmlCommentPreProcessor;
use ALI\Processors\PreProcessors\IgnoreHtmlTagsPreProcessor;
use ALI\Processors\PreProcessors\SliIgnoreTagPreProcessor;
use ALI\Processors\ProcessorsManager;
use ALI\Processors\TranslateProcessors\HtmlAttributesProcessor;
use ALI\Processors\TranslateProcessors\HtmlTagProcessor;
use ALI\Processors\TranslateProcessors\SimpleTextProcessor;
use ALI\Translate\Language\Language;
use ALI\Translate\Sources\CsvFileSource;
use ALI\Translate\Sources\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translate\Sources\Installers\MySqlSourceInstaller;
use ALI\Translate\Sources\MySqlSource;
use ALI\Translate\Translators\Translator;
use PDO;

/**
 * Class
 */
class ALIAbFactory
{
    /**
     * @param PDO $connection
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return ALIAbc
     */
    public function createALIByHtmlBufferMysqlSource(PDO $connection, $originalLanguageAlias, $currentLanguageAlias)
    {
        $translator = $this->generateMysqlTranslator($connection, $originalLanguageAlias, $currentLanguageAlias);

        $processorsManager = $this->generateBaseHtmlProcessorManager();

        return new ALIAbc($translator, $processorsManager);
    }

    /**
     * @param PDO $connection
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return ALIAbc
     */
    public function createALIByMysqlSource(PDO $connection, $originalLanguageAlias, $currentLanguageAlias)
    {
        $translator = $this->generateMysqlTranslator($connection, $originalLanguageAlias, $currentLanguageAlias);

        return new ALIAbc($translator);
    }

    /**
     * @param $translationDirectoryPath
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return ALIAbc
     * @throws UnsupportedLanguageAliasException
     */
    public function createALIByHtmlBufferCsvSource($translationDirectoryPath, $originalLanguageAlias, $currentLanguageAlias)
    {
        $translator = $this->generateCsvTranslator($translationDirectoryPath, $originalLanguageAlias, $currentLanguageAlias);

        $processorsManager = $this->generateBaseHtmlProcessorManager();

        return new ALIAbc($translator, $processorsManager);
    }

    /**
     * @param $translationDirectoryPath
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return ALIAbc
     * @throws UnsupportedLanguageAliasException
     */
    public function createALIByCsvSource($translationDirectoryPath, $originalLanguageAlias, $currentLanguageAlias)
    {
        $translator = $this->generateCsvTranslator($translationDirectoryPath, $originalLanguageAlias, $currentLanguageAlias);

        return new ALIAbc($translator);
    }

    /**
     * @param PDO $connection
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return Translator
     */
    private function generateMysqlTranslator(PDO $connection, $originalLanguageAlias, $currentLanguageAlias)
    {
        $originalLanguage = new Language($originalLanguageAlias);

        $source = new MySqlSource($connection, $originalLanguage);
        $sourceInstaller = new MySqlSourceInstaller($connection);
        if ($sourceInstaller->isInstalled()) {
            $sourceInstaller->install();
        }

        $currentLanguage = new Language($currentLanguageAlias);

        return new Translator($currentLanguage, $source);
    }

    /**
     * @return ProcessorsManager
     */
    private function generateBaseHtmlProcessorManager()
    {
        $processorsManager = new ProcessorsManager();

        $processorsManager->addPreProcessor(new HtmlCommentPreProcessor());
        $processorsManager->addPreProcessor(new IgnoreHtmlTagsPreProcessor());
        $processorsManager->addPreProcessor(new SliIgnoreTagPreProcessor());

        $processorsManager->addTranslateProcessor(new HtmlTagProcessor());
        $processorsManager->addTranslateProcessor(new HtmlAttributesProcessor());
        $processorsManager->addTranslateProcessor(new SimpleTextProcessor());

        return $processorsManager;
    }

    /**
     * @param $translationDirectoryPath
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return Translator
     * @throws UnsupportedLanguageAliasException
     */
    private function generateCsvTranslator($translationDirectoryPath, $originalLanguageAlias, $currentLanguageAlias)
    {
        $currentLanguage = new Language($currentLanguageAlias);
        $originalLanguage = new Language($originalLanguageAlias);

        $source = new CsvFileSource($translationDirectoryPath, $originalLanguage);
        $fileCsvPath = $source->getLanguageFilePath($currentLanguage->getAlias());
        if (!file_exists($fileCsvPath)) {
            touch($fileCsvPath);
        }

        return new Translator($currentLanguage, $source);
    }
}
