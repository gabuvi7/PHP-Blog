CREATE DATABASE IF NOT EXISTS api_rest_laravel;
USE api_rest_laravel;

CREATE TABLE tblUsers(
idUser              int(255) auto_increment not null,
name            varchar(50) NOT NULL,
surname         varchar(100),
role            varchar(20),
email           varchar(255) NOT NULL,
password        varchar(255) NOT NULL,
description     text,
image           varchar(255),
created_at      datetime DEFAULT NULL,
updated_at      datetime DEFAULT NULL,
remember_token  varchar(255),
CONSTRAINT pk_users PRIMARY KEY(idUser)
)ENGINE=InnoDb;

CREATE TABLE tblCategories(
idCategory              int(255) auto_increment not null,
name            varchar(100),
created_at      datetime DEFAULT NULL,
updated_at      datetime DEFAULT NULL,
CONSTRAINT pk_categories PRIMARY KEY(idCategory)
)ENGINE=InnoDb;

CREATE TABLE tblPosts(
idPost              int(255) auto_increment not null,
user_id         int(255) not null,
category_id     int(255) not null,
title           varchar(255) not null,
content         text not null,
image           varchar(255),
created_at      datetime DEFAULT NULL,
updated_at      datetime DEFAULT NULL,
CONSTRAINT pk_posts PRIMARY KEY(idPost),
CONSTRAINT fk_post_user FOREIGN KEY(user_id) REFERENCES tblUsers(idUser),
CONSTRAINT fk_post_category FOREIGN KEY(category_id) REFERENCES tblCategories(idCategory)
)ENGINE=InnoDb;