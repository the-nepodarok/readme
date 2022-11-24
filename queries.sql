-- создаём двоих пользователей ~(инь и янь)~
INSERT INTO user
SET
  email = 'the-nepodarok@github.com',
  user_name = 'the-nepodarok',
  password = 'HtmlKeks'
;

INSERT INTO user
SET
  email = 'the-podarok@github.com',
  user_name = 'the-podarok',
  password = 'KeksHtml'
;

-- заполняем таблицу типов всеми возможными типами будущих записей
INSERT INTO content_type
SET type_name = 'Текст', type_val = 'text';

INSERT INTO content_type
SET type_name = 'Цитата', type_val = 'quote';

INSERT INTO content_type
SET type_name = 'Картинка', type_val = 'photo';

INSERT INTO content_type
SET type_name = 'Видео', type_val = 'video';

INSERT INTO content_type
SET type_name = 'Ссылка', type_val = 'link';

-- заносим в таблицу постов пять записей разного типа, взятых из массива $posts
INSERT INTO post
SET
  header = 'Цитата',
  text_content = 'Мы в жизни любим только раз, а после ищем лишь похожих',
  quote_origin = 'Неизвестный автор',
  view_count = 42,
  user_id = 1,
  content_type_id = 1
;

INSERT INTO post
SET
  header = 'Игра Престолов',
  text_content = 'Не могу дождаться начала финального сезона своего любимого сериала!',
  view_count = 666,
  user_id = 2,
  content_type_id = 2
;

INSERT INTO post
SET
  header = 'Наконец, обработал фотки!',
  picture = 'http://readme/img/rock-medium.jpg',
  view_count = 0,
  user_id = 1,
  content_type_id = 3
;

INSERT INTO post
SET
  header = 'Моя мечта',
  picture = 'http://readme/img/coast-medium.jpg',
  view_count = 10,
  user_id = 2,
  content_type_id = 3
;

INSERT INTO post
SET
  header = 'Лучшие курсы',
  link = 'http://www.htmlacademy.ru/',
  view_count = 1000,
  user_id = 1,
  content_type_id = 5
;

-- "пишем" по комментарию к двум разным записям
INSERT INTO comment
SET
  comment_content = 'Зря ждали, расходимся. Весь сериал запороли, как только могли',
  user_id = 1,
  post_id = 2
;

INSERT INTO comment
SET
  comment_content = 'Если я правильно помню, автор цитаты - Дэвид Бекхэм',
  user_id = 2,
  post_id = 1
;

-- Выводим список постов, отсортированных по популярности, с именами пользователей и типом поста
SELECT p.id, type_name, user_name, view_count
FROM post p
JOIN user u ON user_id = u.id
JOIN content_type c ON content_type_id = c.id
ORDER BY view_count DESC;

-- Выводим список постов конкретного пользователя
SELECT p.id, header, user_name FROM post p
JOIN user u ON user_id = u.id
WHERE user_name = 'the-nepodarok';

-- Выводим список комментариев к конкретному посту с отображением имени пользователя
SELECT c.id, comment_content, user_name FROM comment c
JOIN post p ON c.post_id = p.id
JOIN user u ON c.user_id = u.id
WHERE post_id = 1;

-- Добавлем один лайк от пользователя 2 к посту под номером 3
INSERT INTO fav_list SET user_id = 2, post_id = 3;
-- Обновляем информацию о добавленном лайке в пост
UPDATE post SET like_count = 1 WHERE post.id = 3;
-- Проверяем количество лайков у поста
SELECT like_count FROM post WHERE post.id = 3;

-- Добавляем пользователю 2 в подписчики пользователя 1
INSERT INTO follower_list
SET following_user_id = 1, followed_user_id = 2;
-- Выводим таблицу с пользователями, у которых есть хотя бы один подписчик, отсортированную по убыванию
SELECT user_name, COUNT(following_user_id) AS f_count FROM follower_list
JOIN user u ON followed_user_id = u.id
WHERE followed_user_id > 0
GROUP BY user_name
ORDER BY f_count DESC;
