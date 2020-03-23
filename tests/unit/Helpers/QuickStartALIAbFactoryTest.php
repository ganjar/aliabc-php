<?php

namespace ALI\Tests\unit\Helpers;

use ALI\ALIAbc;
use ALI\Helpers\QuickStartALIAbFactory;
use PHPUnit\Framework\TestCase;

/**
 * QuickStartALIAbFactory
 */
class QuickStartALIAbFactoryTest extends TestCase
{
    /**
     * Test
     */
    public function test()
    {
        $quickStart = new QuickStartALIAbFactory();

        $originalLanguageAlias = 'en';
        $currentLanguageAlias = 'ua';

        $connection = new \PDO(SOURCE_MYSQL_DNS, SOURCE_MYSQL_USER, SOURCE_MYSQL_PASSWORD);

        $csvSourceALIAb = $quickStart->createCsvSource(SOURCE_CSV_PATH, $originalLanguageAlias, $currentLanguageAlias);
        $this->assertInstanceOf(ALIAbc::class, $csvSourceALIAb);

        $htmlBufferCsvSourceALIAb = $quickStart->createHtmlBufferCsvSource(SOURCE_CSV_PATH, $originalLanguageAlias, $currentLanguageAlias);
        $this->assertInstanceOf(ALIAbc::class, $htmlBufferCsvSourceALIAb);

        $mysqlSourceALIAb = $quickStart->createMysqlSource($connection, $originalLanguageAlias, $currentLanguageAlias);
        $this->assertInstanceOf(ALIAbc::class, $mysqlSourceALIAb);

        $htmlBufferMysqlSource = $quickStart->createHtmlBufferMysqlSource($connection, $originalLanguageAlias, $currentLanguageAlias);
        $this->assertInstanceOf(ALIAbc::class, $htmlBufferMysqlSource);
    }
}
