-- --
-- Setup ACL package db tables for SQLite.
--
-- @author Rob Tuley
-- @version SVN: $Id$
-- --

-- Users

CREATE TABLE person
(
id INTEGER PRIMARY KEY ASC, -- alias for sqlite rowid
email  TEXT COLLATE NOCASE NOT NULL
);
CREATE UNIQUE INDEX person_email_idx ON person (email);

-- Roles

CREATE TABLE role
(
id INTEGER PRIMARY KEY ASC, -- alias for sqlite rowid
name TEXT COLLATE NOCASE NOT NULL
);
CREATE UNIQUE INDEX role_name_idx ON role (name);

CREATE TABLE role_group
(
role INTEGER,
member INTEGER,
PRIMARY KEY (role,member)
);

-- Association between people and roles

CREATE TABLE person_role
(
person INTEGER,
role INTEGER,
PRIMARY KEY (person,role)
);

-- Auth tokens

CREATE TABLE person_auth_token
(
token TEXT PRIMARY KEY,
person INTEGER NOT NULL,
expiry INTEGER NOT NULL -- unix time
);

-- Auth hammer log

CREATE TABLE person_hammer_lock
(
person INTEGER,
ip INTEGER,
fail_count INTEGER,
expiry INTEGER
);

-- Word list

CREATE TABLE pwd_dictionary
(
word TEXT PRIMARY KEY
);

-- Foreign key constraints

CREATE TRIGGER fkd_role_group_role
BEFORE DELETE ON role
FOR EACH ROW BEGIN
    DELETE from role_group WHERE role = OLD.id OR member = OLD.id; -- cascade delete
END;

CREATE TRIGGER fkd_person_role_role
BEFORE DELETE ON role
FOR EACH ROW BEGIN
    DELETE from person_role WHERE role = OLD.id; -- cascade delete
END;

CREATE TRIGGER fkd_person_role_person
BEFORE DELETE ON person
FOR EACH ROW BEGIN
    DELETE from person_role WHERE person = OLD.id; -- cascade delete
END;

CREATE TRIGGER fkd_person_auth_token
BEFORE DELETE ON person
FOR EACH ROW BEGIN
    DELETE from person_auth_token WHERE person = OLD.id; -- cascade delete
END;

CREATE TRIGGER fkd_person_hammer_lock
BEFORE DELETE ON person
FOR EACH ROW BEGIN
    DELETE from person_hammer_lock WHERE person = OLD.id; -- cascade delete
END;
