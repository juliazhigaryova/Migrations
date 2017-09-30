CREATE TABLE `user` (
`id` INT(11) UNSIGNED NOT NULL auto_increment,
`name` VARCHAR(30) NOT NULL,
`login` VARCHAR(30) NOT NULL UNIQUE,
  `password` VARCHAR(32) NOT NULL,
  PRIMARY KEY (id)
)
  ENGINE = innodb
  AUTO_INCREMENT = 1
  CHARACTER SET utf8
  COLLATE utf8_general_ci