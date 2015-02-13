-- assuming we're in the webapp directory, run
-- sqlite3 data/test.db < data/db.sql 


-- create our table

CREATE TABLE IF NOT EXISTS 'users' (
    -- we use SQLite3's built-in "rowid" column rather than explicitly defining an auto-incrementing Primary Key
    'email' VARCHAR(140) NOT NULL,
    'password' VARCHAR(140) NOT NULL
);

