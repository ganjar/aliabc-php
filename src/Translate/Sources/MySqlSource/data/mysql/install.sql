CREATE TABLE ali_language (
  alias                VARCHAR(4)          NOT NULL,
  is_active            TINYINT(1)          NOT NULL DEFAULT 0,
  title                VARCHAR(64) NOT NULL DEFAULT '',
  auto_translate_alias VARCHAR(8)                   DEFAULT NULL,
  PRIMARY KEY (alias),
  UNIQUE INDEX UK_ali_lang_alias (alias)
)
  ENGINE = INNODB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

CREATE TABLE ali_original (
  id      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  content_index VARCHAR(64) BINARY CHARACTER SET utf8mb4
          COLLATE utf8mb4_bin NOT NULL COMMENT 'System column for indexation',
  content TEXT             NOT NULL,
  PRIMARY KEY (id),
  INDEX indexContentIndex (content_index)
)
  ENGINE = INNODB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

CREATE TABLE ali_translate (
  original_id INT(11) UNSIGNED    NOT NULL,
  language_alias VARCHAR(4) NOT NULL,
  content     TEXT                NOT NULL,
  PRIMARY KEY (original_id, language_alias),
  INDEX IDX_ali_translate_original_id (original_id),
  CONSTRAINT FK_ali_translate_ali_original_id FOREIGN KEY (original_id)
  REFERENCES ali_original (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = INNODB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
