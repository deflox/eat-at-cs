-- -----------------------------------------------------
-- Table `leorudin_projects`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `leorudin_projects`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(80) NOT NULL,
  `email` VARCHAR(60) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `locked` TINYINT(4) NOT NULL,
  `verified` TINYINT(4) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `leorudin_projects`.`keywords`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `leorudin_projects`.`keywords` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `type` TINYINT(4) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `leorudin_projects`.`user_keyword`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `leorudin_projects`.`user_keyword` (
  `user_id` INT NOT NULL,
  `keyword_id` INT NOT NULL,
  PRIMARY KEY (`user_id`, `keyword_id`),
  INDEX `fk_users_has_keywords_keywords1_idx` (`keyword_id` ASC),
  INDEX `fk_users_has_keywords_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_users_has_keywords_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `leorudin_projects`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_has_keywords_keywords1`
    FOREIGN KEY (`keyword_id`)
    REFERENCES `leorudin_projects`.`keywords` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `leorudin_projects`.`tokens`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `leorudin_projects`.`tokens` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `value` VARCHAR(255) NOT NULL,
  `user_id` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_tokens_users1_idx` (`user_id` ASC),
  CONSTRAINT `fk_tokens_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `leorudin_projects`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `leorudin_projects`.`attempts`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `leorudin_projects`.`attempts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(255) NOT NULL,
  `count` TINYINT(4) NOT NULL,
  `lock_time` TIMESTAMP NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;