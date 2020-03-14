<?php

namespace ALI\Translate\Sources;

use ALI\Translate\Sources\Exceptions\SourceException;
use PDO;
use ALI\Translate\Language\LanguageInterface;
use ALI\Translate\Sources\Exceptions\MySqlSource\LanguageNotExistsException;

/**
 * Class MySqlSource
 * @package ALI\Sources
 */
class MySqlSource implements SourceInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var LanguageInterface
     */
    private $originalLanguage;

    /**
     * @var string
     */
    private $originalTableName;

    /**
     * @var string
     */
    private $translateTableName;

    /**
     * @param PDO $pdo
     * @param LanguageInterface $originalLanguage
     * @param string $originalTableName
     * @param string $translateTableName
     */
    public function __construct(
        PDO $pdo,
        LanguageInterface $originalLanguage,
        $originalTableName = 'ali_original',
        $translateTableName = 'ali_translate'
    )
    {
        $this->pdo = $pdo;
        $this->originalLanguage = $originalLanguage;
        $this->originalTableName = $originalTableName;
        $this->translateTableName = $translateTableName;
    }

    /**
     * @return LanguageInterface
     */
    public function getOriginalLanguage()
    {
        return $this->originalLanguage;
    }

    /**
     * @param string $phrase
     * @param LanguageInterface $language
     * @return string
     * @throws SourceException
     */
    public function getTranslate($phrase, LanguageInterface $language)
    {
        $translates = $this->getTranslates([$phrase], $language);
        if ($translates) {
            return current($translates);
        }

        throw new SourceException('Empty list of translated phrases');
    }

    /**
     * @param array $phrases
     * @param LanguageInterface $language
     * @return array
     */
    public function getTranslates(array $phrases, LanguageInterface $language)
    {
        if ($language->getAlias() === $this->originalLanguage->getAlias()) {
            // TODO check if it's correct response
            return array_combine($phrases, $phrases);
        }

        $countPhrases = count($phrases);

        $whereQuery = [];
        $valuesForWhereBinding = [];
        foreach ($phrases as $keyForBinding => $phrase) {
            $contentIndexKey = 'content_index_' . $keyForBinding;
            $contentKey = 'content_' . $keyForBinding;
            $valuesForWhereBinding[$keyForBinding] = [
                'phrase' => $phrase,
                'contentIndexKey' => $contentIndexKey,
                'contentKey' => $contentKey,
            ];
            $whereQuery[$keyForBinding] = '(o.`content_index`=:' . $contentIndexKey . ' AND BINARY o.`content`=:' . $contentKey . ')';
        }

        $dataQuery = $this->pdo->prepare(
            'SELECT o.`id`, o.`content_index`, o.`content` as `original`, t.`content` as `translate`
                FROM `' . $this->originalTableName . '` AS `o`
                FORCE INDEX(indexContentIndex)
                LEFT JOIN `' . $this->translateTableName . '` AS `t` ON(`o`.`id`=`t`.`original_id` AND `t`.`language_alias`=:languageAlias)
            WHERE ' . implode(' OR ', $whereQuery) . '
            LIMIT ' . $countPhrases
        );
        $dataQuery->bindValue('languageAlias', $language->getAlias(), \PDO::PARAM_INT);

        foreach ($valuesForWhereBinding as $dataForBinding) {
            $originalQueryParams = $this->createOriginalQueryParams($phrase);

            $contentIndexKey = $dataForBinding['contentIndexKey'];
            $contentIndex = $originalQueryParams['contentIndex'];
            $contentKey = $dataForBinding['contentKey'];
            $content = $originalQueryParams['content'];

            $dataQuery->bindValue($contentIndexKey, $contentIndex, \PDO::PARAM_STR);
            $dataQuery->bindValue($contentKey, $content, \PDO::PARAM_STR);
        }

        $dataQuery->execute();

        $translates = [];
        while ($translateRow = $dataQuery->fetch(PDO::FETCH_ASSOC)) {
            $translates[$translateRow['original']] = $translateRow['translate'];
        }

        //phrases that aren't in the database
        foreach ($phrases as $phrase) {
            if (!array_key_exists($phrase, $translates)) {
                $translates[$phrase] = '';
            }
        }

        return $translates;
    }

    /**
     * Generate keys for find original phrase in database
     * @param string $phrase
     * @return array
     */
    protected function createOriginalQueryParams($phrase)
    {
        $contentIndex = mb_substr($phrase, 0, 64, 'utf8');

        return [
            'contentIndex' => $contentIndex,
            'content' => $phrase,
        ];
    }

    /**
     * @param LanguageInterface $language
     * @param string $original
     * @param string $translate
     * @throws LanguageNotExistsException
     */
    public function saveTranslate(LanguageInterface $language, $original, $translate)
    {
        $originalId = $this->getOriginalId($original);
        if (!$originalId) {
            $originalId = $this->insertOriginal($original);
        }

        $this->saveTranslateByOriginalId($language, $originalId, $translate);
    }

    /**
     * @param LanguageInterface $language
     * @return int
     */
    public function getLanguageId(LanguageInterface $language)
    {
        $statement = $this->pdo->prepare("
                SELECT id FROM ali_language WHERE alias=:alias
            ");
        $statement->bindValue('alias', $language->getAlias());
        $statement->execute();
        $language = $statement->fetch(PDO::FETCH_COLUMN);

        return $language;
    }

    /**
     * @param string $original
     * @return mixed
     */
    public function getOriginalId($original)
    {
        $statement = $this->pdo->prepare('
                SELECT id FROM `' . $this->originalTableName . '` WHERE content_index=:contentIndex AND content=:content
            ');
        $queryParams = $this->createOriginalQueryParams($original);
        foreach ($queryParams as $queryKey => $queryParam) {
            $statement->bindValue($queryKey, $queryParam);
        }
        $statement->execute();
        $originalId = $statement->fetch(PDO::FETCH_COLUMN);

        return $originalId;
    }

    /**
     * @param string $original
     * @return string
     */
    public function insertOriginal($original)
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO `' . $this->originalTableName . '` (`content_index`, `content`) VALUES (:contentIndex, :content)'
        );

        $queryParams = $this->createOriginalQueryParams($original);
        foreach ($queryParams as $queryKey => $queryParam) {
            $statement->bindValue($queryKey, $queryParam);
        }

        $statement->execute();

        return $this->pdo->lastInsertId();
    }

    /**
     * Delete original and all translated phrases
     * @param string $original
     */
    public function delete($original)
    {
        $statement = $this->pdo->prepare('
                DELETE FROM `' . $this->originalTableName . '` WHERE content_index=:contentIndex AND content=:content
            ');
        $queryParams = $this->createOriginalQueryParams($original);
        foreach ($queryParams as $queryKey => $queryParam) {
            $statement->bindValue($queryKey, $queryParam);
        }
        $statement->execute();
    }

    /**
     * @param LanguageInterface $language
     * @param int $originalId
     * @param string $translate
     * @throws LanguageNotExistsException
     */
    public function saveTranslateByOriginalId(LanguageInterface $language, $originalId, $translate)
    {
        $updatePdo = $this->pdo->prepare('
                INSERT INTO `' . $this->translateTableName . '` (`original_id`, `language_alias`, `content`)
                VALUES (:id, :languageAlias, :content)
                ON DUPLICATE KEY UPDATE `content`=:content
            ');
        $updatePdo->bindParam(':content', $translate, PDO::PARAM_STR);
        $updatePdo->bindParam(':id', $originalId, PDO::PARAM_INT);
        $languageAlias = $language->getAlias();
        $updatePdo->bindParam(':languageAlias', $languageAlias, PDO::PARAM_STR);
        $updatePdo->execute();
    }
}
