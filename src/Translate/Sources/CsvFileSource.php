<?php

namespace ALI\Translate\Sources;

use ALI\Translate\Language\LanguageInterface;
use ALI\Translate\Sources\Exceptions\CsvFileSource\DirectoryNotFoundException;
use ALI\Translate\Sources\Exceptions\CsvFileSource\FileNotWritableException;
use ALI\Translate\Sources\Exceptions\CsvFileSource\FileReadPermissionsException;
use ALI\Translate\Sources\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;

/**
 * Source for simple translation storage. Directory with text files.
 * File names - must be in format language_alias.file_extension.
 * Language alias - allowed only word symbols and "-_"
 * Content in files - first original and after delimiter - translate.
 * Class FileSource
 * @package ALI\Translate\Sources
 */
class CsvFileSource extends FileSourceAbstract
{
    /**
     * @var string
     */
    protected $directoryPath;

    /**
     * @var LanguageInterface
     */
    private $originalLanguage;

    /**
     * CSV delimiter - only one symbol
     * @var string
     */
    protected $delimiter;
    /**
     * @var string
     */
    protected $filesExtension;

    /**
     * @var array
     */
    protected $allTranslates = [];

    /**
     * FileSource constructor.
     * @param string $directoryPath - Directory with source files
     * @param LanguageInterface $originalLanguage
     * @param string $delimiter - CSV delimiter may be only one symbol
     * @param string $filesExtension
     */
    public function __construct($directoryPath, $originalLanguage, $delimiter = ',', $filesExtension = 'csv')
    {
        $this->directoryPath = rtrim($directoryPath, '/\\');
        $this->originalLanguage = $originalLanguage;
        $this->delimiter = $delimiter;
        $this->filesExtension = $filesExtension;
    }

    /**
     * @return LanguageInterface
     */
    public function getOriginalLanguage()
    {
        return $this->originalLanguage;
    }

    /**
     * @return string
     */
    public function getDirectoryPath()
    {
        return $this->directoryPath;
    }

    /**
     * @param $languageAlias
     * @return string
     * @throws UnsupportedLanguageAliasException
     */
    public function getLanguageFilePath($languageAlias)
    {
        if (preg_match('#[^\w_\-]#uis', $languageAlias)) {
            throw new UnsupportedLanguageAliasException('Unsupported language alias');
        }

        return $this->getDirectoryPath() . DIRECTORY_SEPARATOR . $languageAlias . '.' . $this->filesExtension;
    }

    /**
     * @param string            $phrase
     * @param LanguageInterface $language
     * @return string
     * @throws FileReadPermissionsException
     * @throws DirectoryNotFoundException
     * @throws UnsupportedLanguageAliasException
     */
    public function getTranslate($phrase, LanguageInterface $language)
    {
        if (!isset($this->allTranslates[$language->getAlias()]) || is_null($this->allTranslates[$language->getAlias()])) {
            $this->allTranslates[$language->getAlias()] = $this->parseLanguageFile($language->getAlias());
        }

        if (isset($this->allTranslates[$language->getAlias()][$phrase])) {
            return $this->allTranslates[$language->getAlias()][$phrase];
        }

        return '';
    }

    /**
     * @param string $languageAlias
     * @return array
     * @throws FileReadPermissionsException
     * @throws DirectoryNotFoundException
     * @throws UnsupportedLanguageAliasException
     */
    protected function parseLanguageFile($languageAlias)
    {
        $translates = [];

        if (!file_exists($this->getDirectoryPath()) || !is_dir($this->getDirectoryPath())) {
            throw new DirectoryNotFoundException('Directory not found ' . $this->getDirectoryPath());
        }

        $languageFile = $this->getLanguageFilePath($languageAlias);

        if (file_exists($languageFile)) {
            if (!is_readable($languageFile)) {
                throw new FileReadPermissionsException('Cannot read file ' . $languageFile);
            }

            $fileResource = fopen($languageFile, 'r');
            while ( ($data = fgetcsv($fileResource, 0, $this->delimiter) ) !== false ) {
                $translates[$data[0]] = isset($data[1]) ? $data[1] : '';
            }
            fclose($fileResource);
        }

        return $translates;
    }

    /**
     * @param string $languageAlias
     * @param array  $translatesData - [original => translate]
     * @throws UnsupportedLanguageAliasException
     * @throws FileNotWritableException
     */
    protected function saveLanguageFile($languageAlias, $translatesData)
    {
        $filePath = $this->getLanguageFilePath($languageAlias);
        if (!is_writable($filePath)) {
            throw new FileNotWritableException('File is not writable ' . $this->getDirectoryPath());
        }

        $fileResource = fopen($filePath, 'w');

        foreach ($translatesData as $original => $translate) {
            fputcsv($fileResource, [$original, $translate], $this->delimiter);
        }

        fclose($fileResource);
    }

    /**
     * @param LanguageInterface $language
     * @param string            $original
     * @param string            $translate
     * @throws DirectoryNotFoundException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     * @throws FileNotWritableException
     */
    public function saveTranslate(LanguageInterface $language, $original, $translate)
    {
        $translates = $this->parseLanguageFile($language->getAlias());
        $translates[$original] = $translate;
        $this->saveLanguageFile($language->getAlias(), $translates);
    }

    /**
     * Delete original and all translated phrases
     * @param string $original
     * @throws DirectoryNotFoundException
     * @throws FileNotWritableException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     */
    public function delete($original)
    {
        $dataFiles = glob($this->getDirectoryPath() . DIRECTORY_SEPARATOR .  '*.' . $this->filesExtension);
        foreach ($dataFiles as $file) {
            $fileInfo = pathinfo($file);
            $languageAlias = $fileInfo['filename'];
            $translates = $this->parseLanguageFile($languageAlias);
            if (key_exists($original, $translates)) {
                unset($translates[$original]);
                $this->saveLanguageFile($languageAlias, $translates);
            }
        }
    }
}
