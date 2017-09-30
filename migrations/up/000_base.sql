CREATE TABLE IF NOT EXISTS `migration` (
  `id` INT(11) UNSIGNED NOT NULL auto_increment,
  `filename` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT current_timestamp,
  PRIMARY KEY (id)
)
  ENGINE = innodb
  AUTO_INCREMENT = 1
  CHARACTER SET utf8
  COLLATE utf8_general_ci