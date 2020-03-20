<?php

namespace ALI\Tests\components\Factories;

use ALI\Translate\Language\Language;
use ALI\Translate\Sources\CsvFileSource;
use ALI\Translate\Sources\Installers\MySqlSourceInstaller;
use ALI\Translate\Sources\MySqlSource;
use PDO;

/**
 * SourceFactory
 */
class SourceFactory
{
    /**
     * @return PDO
     */
    public function createPDO()
    {
        $connection = new PDO('mysql:dbname=test;host=mysql', 'root', 'root');
        $connection->setAttribute(PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION);

        return $connection;
    }

    /**
     * @param Language $originalLanguage
     * @return array [MySqlSource, MySqlSourceInstaller]
     */
    public function createMysqlSource(Language $originalLanguage)
    {
        $connection = $this->createPDO();
        $mySqlSourceInstaller = new MySqlSourceInstaller($connection);
        if (!$mySqlSourceInstaller->isInstalled()) {
            $mySqlSourceInstaller->install();
        }

        $mySqlSource = new MySqlSource($connection, $originalLanguage);

        return [$mySqlSource, $mySqlSourceInstaller];
    }

    /**
     * @param Language $originalLanguage
     * @return CsvFileSource
     */
    public function createCsvSource(Language $originalLanguage)
    {
        $dataDirectory = TEST_DATA_PATH . DIRECTORY_SEPARATOR . 'source' . DIRECTORY_SEPARATOR . 'csv';
        $csvFileSource = new CsvFileSource($dataDirectory, $originalLanguage);

        return $csvFileSource;
    }
}
