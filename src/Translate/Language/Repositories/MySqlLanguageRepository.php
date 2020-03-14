<?php

namespace ALI\Translate\Language\Repositories;

use ALI\Translate\Language\Language;
use ALI\Translate\Language\LanguageRepositoryInterface;

/**
 * MySqlLanguageRepository
 */
class MySqlLanguageRepository implements LanguageRepositoryInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $languageTableName;

    /**
     * @param \PDO $pdo
     * @param string $languageTableName
     */
    public function __construct(\PDO $pdo, $languageTableName = 'ali_language')
    {
        $this->pdo = $pdo;
        $this->languageTableName = $languageTableName;
    }

    /**
     * @param Language $language
     * @return bool
     */
    public function save(Language $language)
    {
        $statement = $this->pdo->prepare('
                INSERT `' . $this->languageTableName . '` (is_active,alias,title) VALUES (1,:alias,:title)
                ON DUPLICATE KEY UPDATE `title`=:title
            ');
        $statement->bindValue('alias', $language->getAlias());
        $statement->bindValue('title', $language->getTitle());

        return $statement->execute();
    }

    /**
     * @param string $alias
     * @return Language|null
     */
    public function findLanguage($alias)
    {
        $statement = $this->pdo->prepare('
                SELECT * FROM `' . $this->languageTableName . '` WHERE alias=:alias AND is_active=1
            ');

        $statement->bindValue('alias', $alias);
        $statement->execute();
        $languageData = $statement->fetch();
        if (!$languageData) {
            return null;
        }

        return $this->generateLanguageObject($languageData);
    }

    /**
     * @return Language[]
     */
    public function getAllLanguages()
    {
        $statement = $this->pdo->prepare('
                SELECT * FROM `' . $this->languageTableName . '` WHERE is_active=1
            ');
        $statement->execute();
        $languagesData = $statement->fetchAll();

        $languages = [];
        foreach ($languagesData as $languageData) {
            $languages[] = $this->generateLanguageObject($languageData);
        }

        return $languages;
    }

    /**
     * @param array $languageData
     * @return Language
     */
    private function generateLanguageObject(array $languageData)
    {
        return new Language($languageData['alias'], $languageData['title']);
    }
}
