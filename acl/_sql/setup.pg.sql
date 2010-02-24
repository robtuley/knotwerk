-- --
-- Setup ACL package db tables for PostgreSQL.
--
-- @author Rob Tuley
-- @version SVN: $Id$
-- --

CREATE TABLE person
(
id SERIAL PRIMARY KEY,
email VARCHAR(200) NOT NULL
);
CREATE UNIQUE INDEX person_email_idx ON person (LOWER(email));

CREATE TABLE role
(
id SERIAL PRIMARY KEY,
name VARCHAR(50) NOT NULL
);
CREATE UNIQUE INDEX role_name_idx ON role (name);

CREATE TABLE role_group
(
role INTEGER NOT NULL REFERENCES role ON DELETE CASCADE,
member INTEGER NOT NULL REFERENCES role ON DELETE CASCADE,
PRIMARY KEY (role,member)
);

CREATE TABLE person_role
(
person INTEGER NOT NULL REFERENCES person ON DELETE CASCADE,
role INTEGER NOT NULL REFERENCES role ON DELETE CASCADE,
PRIMARY KEY (person,role)
);

CREATE TABLE person_auth_token
(
token CHAR(32) PRIMARY KEY,
person INTEGER NOT NULL REFERENCES person ON DELETE CASCADE,
expiry BIGINT NOT NULL
);

CREATE TABLE person_hammer_lock
(
person INTEGER REFERENCES person ON DELETE CASCADE,
ip BIGINT,
fail_count SMALLINT NOT NULL,
expiry BIGINT
);
CREATE UNIQUE INDEX person_hammer_lock_person_idx ON person_hammer_lock (person);
CREATE UNIQUE INDEX person_hammer_lock_ip_idx ON person_hammer_lock (ip);

CREATE TABLE pwd_dictionary
(
word VARCHAR(50) PRIMARY KEY
);
