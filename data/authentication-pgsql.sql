-- First, run framework/web/auth/schema-pgsql.sql.
-- Next, run the create table statements below.
CREATE SEQUENCE sq_icauser_id;
CREATE TABLE icauser (
    id INT NOT NULL DEFAULT NEXTVAL('sq_icauser_id'),
    email VARCHAR(512) NOT NULL,
    salt VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    PRIMARY KEY(id)
);
ALTER TABLE icauser ADD UNIQUE (email);

CREATE SEQUENCE sq_icaresetpassword_id;
CREATE TABLE icaresetpassword (
    id INT NOT NULL DEFAULT NEXTVAL('sq_icaresetpassword_id'),
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    userid INT NOT NULL ,
    request_token VARCHAR(512) NOT NULL ,
    reset_token VARCHAR(512) NULL,
    PRIMARY KEY (id) ,
    CONSTRAINT fk_icaresetpassword_userid
    FOREIGN KEY (userid )
    REFERENCES icauser (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);


    
