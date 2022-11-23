CREATE DATABASE readme
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE readme;

CREATE TABLE user (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  create_dt DATETIME DEFAULT CURRENT_TIMESTAMP,
  email     VARCHAR(128) NOT NULL UNIQUE,
  user_name VARCHAR(64) NOT NULL COMMENT 'имя пользователя',
  password  CHAR(255)    NOT NULL,
  avatar    VARCHAR(255),
  INDEX (user_name)
);

CREATE TABLE content_type (
  id        TINYINT AUTO_INCREMENT PRIMARY KEY,
  type_name VARCHAR(20) UNIQUE COMMENT 'название типа',
  type_icon_class VARCHAR(16) UNIQUE COMMENT 'класс иконки типа'
);

CREATE TABLE hashtag (
  id      INT AUTO_INCREMENT PRIMARY KEY,
  hashtag VARCHAR(20) UNIQUE
) COMMENT 'таблица со всеми хэштегами';

CREATE TABLE post (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  create_dt    DATETIME DEFAULT CURRENT_TIMESTAMP,
  header       VARCHAR(128) COMMENT 'заголовок поста',
  text_content TEXT,
  quote_origin VARCHAR(128) COMMENT 'автор/источник цитаты',
  picture      VARCHAR(255),
  video        VARCHAR(255) COMMENT 'ссылка на видео на YouTube',
  link         VARCHAR(255),
  view_count   INT DEFAULT 0,
  user_id      INT,
  is_repost    BOOLEAN DEFAULT FALSE,
  origin_user_id INT COMMENT 'поле «автор оригинальной записи», заполнится, если is_repost = true',
  content_type_id      TINYINT,
  FOREIGN KEY (user_id) REFERENCES user (id),
  FOREIGN KEY (content_type_id) REFERENCES content_type (id),
  INDEX (view_count) COMMENT 'индекс для сортировки по просмотрам'
);

CREATE TABLE post_hashtag_list (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT,
  hashtag_id INT,
  FOREIGN KEY (post_id) REFERENCES post (id),
  FOREIGN KEY (hashtag_id) REFERENCES hashtag (id),
  UNIQUE KEY (post_id, hashtag_id),
  INDEX post_hashtag (post_id, hashtag_id) COMMENT 'индекс пар пост-хэштег'
) COMMENT 'связывание хэштегов и постов';

CREATE TABLE comment (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  create_dt       DATETIME DEFAULT CURRENT_TIMESTAMP,
  comment_content TEXT,
  user_id         INT,
  post_id         INT,
  FOREIGN KEY (user_id) REFERENCES user (id),
  FOREIGN KEY (post_id) REFERENCES post (id)
);

CREATE TABLE fav_list (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT COMMENT 'кто лайкает',
  post_id INT COMMENT 'что лайкают',
  FOREIGN KEY (user_id) REFERENCES user (id),
  FOREIGN KEY (post_id) REFERENCES post (id),
  UNIQUE KEY (user_id, post_id),
  INDEX likes (user_id, post_id) COMMENT 'индекс для сортировки по лайкам'
) COMMENT 'лайки (избранное)';

CREATE TABLE follower_list (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  following_user_id INT COMMENT 'кто подписывается',
  followed_user_id  INT COMMENT 'на кого подписывается',
  FOREIGN KEY (following_user_id) REFERENCES user (id),
  FOREIGN KEY (followed_user_id) REFERENCES user (id),
  UNIQUE KEY (followed_user_id, following_user_id) COMMENT 'уникальные пары пользователь-подписчик',
  INDEX subscription (following_user_id, followed_user_id) COMMENT 'индекс пар пользователь-подписчик'
) COMMENT 'подписки на пользователей';

CREATE TABLE message (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  create_dt       DATETIME DEFAULT CURRENT_TIMESTAMP,
  message_content TEXT,
  sender_id       INT COMMENT 'отправитель',
  receiver_id     INT COMMENT 'получатель',
  FOREIGN KEY (sender_id) REFERENCES user (id),
  FOREIGN KEY (receiver_id) REFERENCES user (id)
);
