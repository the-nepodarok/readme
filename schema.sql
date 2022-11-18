CREATE DATABASE readme
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE readme;

CREATE TABLE user (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  create_dt DATETIME DEFAULT CURRENT_TIMESTAMP,
  email     VARCHAR(128) NOT NULL UNIQUE,
  user_name VARCHAR(64)  NOT NULL, # имя пользователя
  password  CHAR(64)     NOT NULL,
  avatar    VARCHAR(255),
  UNIQUE INDEX (email, user_name)
);

CREATE TABLE content_type ( # таблица со всеми типами постов
  id        TINYINT AUTO_INCREMENT PRIMARY KEY,
  type_name VARCHAR(20), # название типа
  type_icon_class VARCHAR(16), # класс иконки типа
  UNIQUE INDEX (type_name)
);

CREATE TABLE hashtag ( # таблица со всеми хэштегами
  id      INT AUTO_INCREMENT PRIMARY KEY,
  hashtag VARCHAR(20) UNIQUE
);

CREATE TABLE post (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  create_dt    DATETIME DEFAULT CURRENT_TIMESTAMP,
  header       VARCHAR(128), # заголовок поста
  text_content TEXT,
  quote_origin VARCHAR(128), # автор/источник цитаты
  picture      VARCHAR(255),
  video        VARCHAR(255), # ссылка на видео на YouTube
  link         VARCHAR(255),
  view_count   INT DEFAULT 0,
  user_id      INT,
  content_type_id      TINYINT,
  FOREIGN KEY (user_id) REFERENCES user (id),
  FOREIGN KEY (content_type_id) REFERENCES content_type (id),
  INDEX (header)
);

CREATE TABLE post_hashtag_list ( # связывание хэштегов и постов
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT,
  hashtag_id INT,
  FOREIGN KEY (post_id) REFERENCES post (id),
  FOREIGN KEY (hashtag_id) REFERENCES hashtag (id)
);

CREATE TABLE comment (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  create_dt       DATETIME DEFAULT CURRENT_TIMESTAMP,
  comment_content TEXT,
  user_id         INT,
  post_id         INT,
  FOREIGN KEY (user_id) REFERENCES user (id),
  FOREIGN KEY (post_id) REFERENCES post (id)
);

CREATE TABLE fav_list ( # лайки (избранное)
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT, # кто лайкает
  post_id INT, # что лайкают
  FOREIGN KEY (user_id) REFERENCES user (id),
  FOREIGN KEY (post_id) REFERENCES post (id)
);

CREATE TABLE follower_list ( # подписки на пользователей
  id                INT AUTO_INCREMENT PRIMARY KEY,
  following_user_id INT UNIQUE, # кто подписывается
  followed_user_id  INT UNIQUE, # на кого подписывается
  FOREIGN KEY (following_user_id) REFERENCES user (id),
  FOREIGN KEY (followed_user_id) REFERENCES user (id)
);

CREATE TABLE message (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  create_dt       DATETIME DEFAULT CURRENT_TIMESTAMP,
  message_content TEXT,
  sender_id       INT, # отправитель
  receiver_id     INT, # получатель
  FOREIGN KEY (sender_id) REFERENCES user (id),
  FOREIGN KEY (receiver_id) REFERENCES user (id)
);

CREATE TABLE repost ( # репосты
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT,
  user_id INT, # кто репостит
  author_id INT, # от кого репостят
  FOREIGN KEY (post_id) REFERENCES post (id),
  FOREIGN KEY (user_id) REFERENCES user (id),
  FOREIGN KEY (author_id) REFERENCES user (id)
);
