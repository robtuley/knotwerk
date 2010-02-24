-- --
-- Teardown ACL package db tables for MySQL.
--
-- @author Rob Tuley
-- @version SVN: $Id$
-- --

DROP TABLE IF EXISTS person_auth_token,person_hammer_lock,person_role,
                     person,role_group,role,pwd_dictionary;
