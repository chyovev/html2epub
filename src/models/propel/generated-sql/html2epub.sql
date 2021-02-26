
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- books
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `books`;

CREATE TABLE `books`
(
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `subtitle` VARCHAR(255),
    `slug` VARCHAR(255) NOT NULL,
    `author` VARCHAR(255),
    `dedication` VARCHAR(255),
    `language_id` int(11) unsigned DEFAULT 0 NOT NULL,
    `publisher` VARCHAR(255),
    `year` int(11) unsigned,
    `isbn` VARCHAR(255),
    `extra_info` TEXT,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `slug` (`slug`),
    INDEX `books_fi_ca578f` (`language_id`),
    CONSTRAINT `books_fk_ca578f`
        FOREIGN KEY (`language_id`)
        REFERENCES `languages` (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- languages
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `languages`;

CREATE TABLE `languages`
(
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `locale` VARCHAR(10) NOT NULL,
    `language` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `locale` (`locale`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;