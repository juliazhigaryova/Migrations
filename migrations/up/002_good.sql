CREATE TABLE `good` (
  `id` INT(11) UNSIGNED NOT NULL auto_increment,
  `title` VARCHAR(150) NOT NULL,
  `weight` INT(3) NOT NULL,
  `price` INT(11) NOT NULL,
  PRIMARY KEY (id)
)
  ENGINE = innodb
  AUTO_INCREMENT = 1
  CHARACTER SET utf8
  COLLATE utf8_general_ci;


  -- Вставка товаров --
INSERT into `good` (`title`, `weight`, `price`) values
  ('Телефон', 270, 25000),
  ('Телефон 2', 310, 37000);