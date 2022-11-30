-- создаём трёх пользователей
INSERT INTO user
    (email, user_name, password, avatar)
  VALUES
    ('vladik@gmail.com', 'Владик', 'oiuy45', 'userpic.jpg'),
    ('larisa@yandex.com', 'Лариса', 'shj4-sk', 'userpic-larisa-small.jpg'),
    ('viktor@yahoo.com', 'Виктор', 'fb2656', 'userpic-mark.jpg');

-- заполняем таблицу типов всеми возможными типами будущих записей
INSERT INTO content_type
    (type_name, type_val)
  VALUES
    ('Текст', 'text'),
    ('Цитата', 'quote'),
    ('Картинка', 'photo'),
    ('Видео', 'video'),
    ('Ссылка',  'link');

-- заносим в таблицу постов пять записей разного типа, взятых из массива $posts
INSERT INTO post
    (header, view_count, user_id, content_type_id, text_content, quote_origin, picture, link)
  VALUES
    ('Цитата', 42, 2, 1, 'Мы в жизни любим только раз, а после ищем лишь похожих', 'Неизвестный автор', NULL, NULL),
    ('Игра Престолов', 666, 1, 2, 'Не могу дождаться начала финального сезона своего любимого сериала!', NULL, NULL, NULL),
    ('Наконец, обработал фотки!', 0, 3, 3, NULL, NULL, 'img/rock-medium.jpg', NULL),
    ('Моя мечта', 10, 1, 3, NULL, NULL, 'img/coast-medium.jpg', NULL),
    ('Лучшие курсы', 1000, 2, 5, NULL, NULL, NULL, 'http://www.htmlacademy.ru/');

-- "пишем" по комментарию к двум разным записям
INSERT INTO comment
    (comment_content, user_id, post_id)
  VALUES
    ('Зря ждали, расходимся. Весь сериал запороли, как только могли', 3, 2),
    ('Если я правильно помню, автор цитаты - Дэвид Бекхэм', 1, 1);

-- Получаем список постов, отсортированных по популярности, с именами пользователей и типом поста
SELECT p.*,
       user_name,
       type_name,
       type_val
FROM post AS p
       JOIN user AS u
            ON p.user_id = u.id
       JOIN content_type AS c
            ON p.content_type_id = c.id
ORDER BY p.view_count DESC;

-- Получаем список постов конкретного пользователя
SELECT * FROM post WHERE user_id = '1';

-- Получаем список комментариев к конкретному посту с отображением имени пользователя
SELECT c.id,
       comment_content,
       user_name
FROM comment AS c
        JOIN user AS u
              ON c.user_id = u.id
WHERE c.post_id = 1;

-- Добавлем один лайк от пользователя 2 к посту под номером 3
INSERT INTO fav_list
    SET user_id = 2,
        post_id = 3;

-- Добавляем пользователю 2 в подписчики пользователя 1
INSERT INTO follower_list
    SET following_user_id = 1,
        followed_user_id = 2;

-- Получаем таблицу с пользователями, у которых есть хотя бы один подписчик, отсортированную по убыванию
SELECT user_name,
       COUNT(following_user_id) AS f_count
FROM follower_list
       JOIN user u
              ON followed_user_id = u.id
GROUP BY user_name
ORDER BY f_count DESC;
