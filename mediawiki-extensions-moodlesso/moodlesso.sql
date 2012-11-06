CREATE TABLE moodlesso (
    id BIGINT NOT NULL AUTO_INCREMENT,
    timestamp BIGINT NOT NULL,
    username CHAR(50) NOT NULL,
    token CHAR(50) NOT NULL,
    url CHAR(200) NOT NULL,
    PRIMARY KEY (id) 
);
