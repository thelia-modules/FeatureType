
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- feature_type
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `feature_type`;

CREATE TABLE `feature_type`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `slug` VARCHAR(50),
    `has_feature_av_value` TINYINT DEFAULT 0,
    `is_multilingual_feature_av_value` TINYINT DEFAULT 0,
    `pattern` VARCHAR(255),
    `css_class` VARCHAR(50),
    `input_type` VARCHAR(25),
    `max` FLOAT,
    `min` FLOAT,
    `step` FLOAT,
    `image_max_width` FLOAT,
    `image_max_height` FLOAT,
    `image_ratio` FLOAT,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `slug_unique` (`slug`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- feature_feature_type
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `feature_feature_type`;

CREATE TABLE `feature_feature_type`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `feature_id` INTEGER NOT NULL,
    `feature_type_id` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `feature_feature_type_unique` (`feature_id`, `feature_type_id`),
    INDEX `FI_feature_feature_type_feature_type_id` (`feature_type_id`),
    CONSTRAINT `fk_feature_feature_type_feature_id`
        FOREIGN KEY (`feature_id`)
        REFERENCES `feature` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_feature_feature_type_feature_type_id`
        FOREIGN KEY (`feature_type_id`)
        REFERENCES `feature_type` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- feature_type_av_meta
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `feature_type_av_meta`;

CREATE TABLE `feature_type_av_meta`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `feature_av_id` INTEGER NOT NULL,
    `feature_feature_type_id` INTEGER NOT NULL,
    `locale` VARCHAR(5) DEFAULT 'en_US' NOT NULL,
    `value` VARCHAR(255),
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `feature_type_av_meta_unique` (`feature_av_id`, `feature_feature_type_id`, `locale`),
    INDEX `FI_feature_av_meta_feature_feature_type_id` (`feature_feature_type_id`),
    CONSTRAINT `fk_feature_av_meta_feature_av_id`
        FOREIGN KEY (`feature_av_id`)
        REFERENCES `feature_av` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_feature_av_meta_feature_feature_type_id`
        FOREIGN KEY (`feature_feature_type_id`)
        REFERENCES `feature_feature_type` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- feature_type_i18n
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `feature_type_i18n`;

CREATE TABLE `feature_type_i18n`
(
    `id` INTEGER NOT NULL,
    `locale` VARCHAR(5) DEFAULT 'en_US' NOT NULL,
    `title` VARCHAR(255),
    `description` LONGTEXT,
    PRIMARY KEY (`id`,`locale`),
    CONSTRAINT `feature_type_i18n_FK_1`
        FOREIGN KEY (`id`)
        REFERENCES `feature_type` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
