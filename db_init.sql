-- db_init.sql
-- Interview task for Flobox
-- Created by Christopher Chaaya 11/08/2016

-- Create the user table
create table users ( id SERIAL, username VARCHAR(40), password VARCHAR, oauth_token VARCHAR, oauth_token_secret VARCHAR, PRIMARY KEY (id));