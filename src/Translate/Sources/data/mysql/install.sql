CREATE TABLE ali_language (
  id                   TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  is_active            TINYINT(1)          NOT NULL DEFAULT 0,
  alias                VARCHAR(8)          NOT NULL,
  title                VARCHAR(64) NOT NULL DEFAULT '',
  auto_translate_alias VARCHAR(8)                   DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX UK_ali_lang_alias (alias)
)
  ENGINE = INNODB
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

CREATE TABLE ali_original (
  id      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  a       VARCHAR(64) BINARY CHARACTER SET utf8
          COLLATE utf8_bin NOT NULL COMMENT 'System column for indexation',
  content TEXT             NOT NULL,
  PRIMARY KEY (id),
  INDEX indexA (a)
)
  ENGINE = INNODB
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

CREATE TABLE ali_translate (
  original_id INT(11) UNSIGNED    NOT NULL,
  language_id TINYINT(3) UNSIGNED NOT NULL,
  content     TEXT                NOT NULL,
  PRIMARY KEY (original_id, language_id),
  INDEX IDX_ali_translate_original_id (original_id),
  CONSTRAINT FK_ali_translate_ali_language_id FOREIGN KEY (language_id)
  REFERENCES ali_language (id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT FK_ali_translate_ali_original_id FOREIGN KEY (original_id)
  REFERENCES ali_original (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = INNODB
  CHARACTER SET utf8
  COLLATE utf8_general_ci;