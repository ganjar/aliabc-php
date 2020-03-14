<?php

namespace ALI\Translate\Sources\MySqlSource;

use PDO;

/**
 * MySqlSourceInstaller
 */
class MySqlSourceInstaller
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return $this->pdo->query(
            'select COUNT(*) from information_schema.tables where table_schema=DATABASE() AND TABLE_NAME="' . $this->originalTableName . '"'
        )->fetchColumn() ? true : false;
    }

    /**
     * @return bool
     */
    public function install()
    {
        return $this->executeSqlFile('install.sql');
    }

    /**
     * Destroy MySql ALI schema
     */
    public function destroy()
    {
        $sqlCommand = [
            'DROP table ali_translate',
            'DROP table ali_original',
            'DROP table ali_language',
        ];
        foreach ($sqlCommand as $sqlCommand) {
            $this->pdo->exec($sqlCommand);
        }
    }

    /**
     * @return string
     */
    private function getMigrationDataDir()
    {
        return implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            'data',
            'mysql',
        ]);
    }

    /**
     * @param $fileName
     * @return bool
     */
    private  function executeSqlFile($fileName)
    {
        $sqlCommands = explode(';', trim(file_get_contents(
            $this->getMigrationDataDir() . DIRECTORY_SEPARATOR . $fileName
        )));

        foreach ($sqlCommands as $sqlCommand) {
            if (!$sqlCommand) {
                continue;
            }
            $this->pdo->exec($sqlCommand);
        }

        return true;
    }
}
