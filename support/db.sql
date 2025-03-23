CREATE DATABASE IF NOT EXISTS kahuna;

USE kahuna;

CREATE TABLE IF NOT EXISTS Product(
    id                  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    serial              VARCHAR(255) NOT NULL,
    name                VARCHAR(255) NOT NULL,
    warrantyLength      INT(11) NOT NULL
);

CREATE TABLE IF NOT EXISTS User(
    id              INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    userName        VARCHAR(255) NOT NULL,
    email           VARCHAR(255) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    accessLevel     CHAR(10) NOT NULL DEFAULT 'user'
);

CREATE TABLE IF NOT EXISTS AccessToken(
    id              INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    userId          INT NOT NULL,
    token           VARCHAR(255) NOT NULL,
    birth           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT c_accesstoken_user
        FOREIGN KEY(userId) REFERENCES User(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);