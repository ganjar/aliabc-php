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
    protected $pdo;

    /**
     * @var string
     */
    protected $languageTableName;

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
     * @param bool $isActive
     * @return bool
     */
    public function save(Language $language, $isActive)
    {
        $isActive = (int)$isActive;
        $statement = $this->pdo->prepare('
                INSERT `' . $this->languageTableName . '` (`is_active`, `alias`, `title`) VALUES (:isActive, :alias, :title)
                ON DUPLICATE KEY UPDATE `title`=:title, `is_active`=:isActive
            ');
        $statement->bindValue('isActive', $isActive);
        $statement->bindValue('alias', $language->getAlias());
        $statement->bindValue('title', $language->getTitle());

        return $statement->execute();
    }

    /**
     * @param string $alias
     * @return Language|null
     */
    public function find($alias)
    {
        $statement = $this->pdo->prepare('
                SELECT * FROM `' . $this->languageTableName . '` WHERE alias=:alias
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
     * @param bool $onlyActive
     * @return Language[]|array
     */
    public function getAll($onlyActive)
    {
        $onlyActive = (int)$onlyActive;
        $statement = $this->pdo->prepare('
                SELECT * FROM `' . $this->languageTableName . '`' . ($onlyActive ? ' WHERE is_active=1' : null) . '
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
    protected function generateLanguageObject(array $languageData)
    {
        return new Language($languageData['alias'], $languageData['title']);
    }
}
