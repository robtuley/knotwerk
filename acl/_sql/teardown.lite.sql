-- --
-- Teardown ACL package db tables for SQLite.
--
-- @author Rob Tuley
-- @version SVN: $Id$
-- --

DROP TABLE IF EXISTS person_auth_token;
DROP TABLE IF EXISTS person_hammer_lock;
DROP TABLE IF EXISTS person_role;
DROP TABLE IF EXISTS person;
DROP TABLE IF EXISTS role_group;
DROP TABLE IF EXISTS role;
DROP TABLE IF EXISTS pwd_dictionary;
