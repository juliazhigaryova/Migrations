CREATE TABLE `category` (
  `id` INT(11) UNSIGNED NOT NULL auto_increment,
  `title` VARCHAR(150) NOT NULL,
  PRIMARY KEY (id)
)
  ENGINE = innodb
  AUTO_INCREMENT = 1
  CHARACTER SET utf8
  COLLATE utf8_general_ci;


INSERT into `category` (`title`) values
  ('Блог'),
  ('Статьи'),
  ('Новости');