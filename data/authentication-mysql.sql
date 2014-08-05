-- First, run framework/web/auth/schema-mysql.sql.
-- Next run the create table statements below.
CREATE TABLE IF NOT EXISTS `icauser` (
    id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(512) NOT NULL,
    salt VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    PRIMARY KEY(id)
    UNIQUE INDEX `icauser_email_UNIQUE` (`email` ASC)
) ENGINE InnoDB;

-- Create the password reset token table.
CREATE TABLE IF NOT EXISTS `icaresetpassword` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `userid` INT NOT NULL,
  `created` DATETIME NOT NULL,
  `request_token` VARCHAR(512) NOT NULL,
  `reset_token` VARCHAR(512) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_password_reset_userid_idx` (`userid` ASC),
  CONSTRAINT `fk_password_reset_userid`
    FOREIGN KEY (`userid`)
    REFERENCES `icauser` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

    
