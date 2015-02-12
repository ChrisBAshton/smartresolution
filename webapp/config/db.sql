-- assuming we're in the webapp directory, run
-- sqlite3 data/test.db < config/db.sql 


-- create our table

CREATE TABLE IF NOT EXISTS 'odr' (
    'user_id' int NOT NULL,
    'email' VARCHAR(140) NOT NULL,
    'password' VARCHAR(140) NOT NULL
);

