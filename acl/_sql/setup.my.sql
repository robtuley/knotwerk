-- --
-- Setup ACL package db tables for MySQL.
--
-- @author Rob Tuley
-- @version SVN: $Id$
-- --

-- Users

CREATE TABLE person
(
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
email VARCHAR(200) NOT NULL,
UNIQUE INDEX person_email_idx (email)
)
ENGINE=InnoDB
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT 'person information';

-- Roles

CREATE TABLE role
(
id MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(50) NOT NULL,
UNIQUE INDEX role_name_idx (name)
)
ENGINE=InnoDB
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT 'possible roles';

CREATE TABLE role_group
(
role MEDIUMINT UNSIGNED NOT NULL,
member MEDIUMINT UNSIGNED NOT NULL,
PRIMARY KEY (role,member)
)
ENGINE=InnoDB
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT 'links roles to each other in groups';

-- Association between persons and roles

CREATE TABLE person_role
(
person INT UNSIGNED NOT NULL,
role MEDIUMINT UNSIGNED NOT NULL,
PRIMARY KEY (person,role)
)
ENGINE=InnoDB
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT 'person permissions';

-- Auth tokens

CREATE TABLE person_auth_token
(
token CHAR(32) PRIMARY KEY,
person INT UNSIGNED NOT NULL,
expiry INT(10) NOT NULL COMMENT 'unix time'
)
ENGINE=InnoDB
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT 'person auth token';

-- Auth hammer log

-- This table is used to log the number of consecutive failed login attempts
-- on both a person accopunt basis and an IP basis. It is then used to detect any
-- hammer attacks on a particular account, or from a particular IP address. The
-- 'fail_count' field records the number of failed logins and if a person account
-- or IP is locked, an expiry time is present in the 'expiry' field.

CREATE TABLE person_hammer_lock
(
person INT UNSIGNED,
ip INT UNSIGNED,
UNIQUE INDEX (person),
UNIQUE INDEX (ip),
fail_count TINYINT UNSIGNED NOT NULL,
expiry INT(10) COMMENT 'unix time'
)
ENGINE=InnoDB
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT 'protection against login hammering';

-- Word list
--
-- This table contains a list of common multi-lingual words from various word
-- list sources and it is used to detect if a password is a real word. If it
-- is, the person can be prompted to choose a different password.

CREATE TABLE pwd_dictionary
(
word VARCHAR(50) PRIMARY KEY
)
ENGINE=MyIsam
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT 'word list of multi-lingual words';

-- Foreign key constraints

ALTER TABLE role_group
ADD CONSTRAINT fk_role_group_role FOREIGN KEY (role)
    REFERENCES role (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
ADD CONSTRAINT fk_role_group_member FOREIGN KEY (member)
    REFERENCES role (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE;

ALTER TABLE person_role
ADD CONSTRAINT fk_person_role_role FOREIGN KEY (role)
    REFERENCES role (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
ADD CONSTRAINT fk_person_role_person FOREIGN KEY (person)
    REFERENCES person (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE;

ALTER TABLE person_auth_token
ADD CONSTRAINT FOREIGN KEY (person)
    REFERENCES person (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE;

ALTER TABLE person_hammer_lock
ADD CONSTRAINT FOREIGN KEY (person)
    REFERENCES person (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE;
