CREATE DATABASE readme
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE readme;

CREATE TABLE users (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  create_dt DATETIME,
  email     VARCHAR(128) NOT NULL UNIQUE,
  login     VARCHAR(64)  NOT NULL UNIQUE,
  password  CHAR(64)     NOT NULL,
  avatar    TEXT
);

CREATE TABLE hashtags (
  id      INT AUTO_INCREMENT PRIMARY KEY,
  hashtag VARCHAR(128)
);

CREATE TABLE content_types (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  type_name VARCHAR(64),
  type_icon VARCHAR(64)
);

CREATE TABLE posts (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  create_dt    DATETIME,
  header       VARCHAR(128),
  text_content TEXT,
  quote_origin VARCHAR(128),
  picture      TEXT,
  video        VARCHAR(128),
  link         TEXT,
  view_count   INT,
  author_id    INT,
  type_id      INT,
  hashtag_id   INT,
  FOREIGN KEY (author_id) REFERENCES users (id),
  FOREIGN KEY (type_id) REFERENCES content_types (id),
  FOREIGN KEY (hashtag_id) REFERENCES hashtags (id)
);

CREATE TABLE comments (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  create_dt       DATETIME,
  comment_content TEXT,
  author_id       INT,
  post_id         INT,
  FOREIGN KEY (author_id) REFERENCES users (id),
  FOREIGN KEY (post_id) REFERENCES posts (id)
);

CREATE TABLE likes (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  like_user_id INT,
  like_post_id INT,
  FOREIGN KEY (like_user_id) REFERENCES users (id),
  FOREIGN KEY (like_post_id) REFERENCES posts (id)
);

CREATE TABLE followers (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  following_user_id INT,
  followed_user_id  INT,
  FOREIGN KEY (following_user_id) REFERENCES users (id),
  FOREIGN KEY (followed_user_id) REFERENCES users (id)
);

CREATE TABLE messages (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  create_dt       DATETIME,
  message_content TEXT,
  sender_id       INT,
  receiver_id     INT,
  FOREIGN KEY (sender_id) REFERENCES users (id),
  FOREIGN KEY (receiver_id) REFERENCES users (id)
);
