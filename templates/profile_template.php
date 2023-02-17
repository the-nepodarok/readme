<?php $auth_user = $_SESSION['user']; // alias для аутентифицированного пользователя ?>
<main class="page__main page__main--profile">
    <h1 class="visually-hidden">Профиль</h1>
    <div class="profile profile--default">
        <div class="profile__user-wrapper">
            <div class="profile__user user container">
                <div class="profile__user-info user__info">
                    <div class="profile__avatar user__avatar">
                        <?php if ($user_data['user_avatar']): ?>
                            <img class="post-details__picture user__picture" src="<?= UPLOAD_PATH . $user_data['user_avatar']; ?>" alt="Аватар пользователя">
                        <?php endif; ?>
                    </div>
                    <div class="profile__name-wrapper user__name-wrapper">
                        <span class="profile__name user__name"><?= $user_data['user_name']; ?></span>
                        <time class="profile__user-time user__time" datetime="<?= $user_data['user_reg_dt']; ?>"><?= format_date($user_data['user_reg_dt']); ?> на сайте</time>
                    </div>
                </div>
                <div class="profile__rating user__rating">
                    <p class="profile__rating-item user__rating-item user__rating-item--publications">
                        <span class="user__rating-amount"><?= $user_data['post_count']; ?></span>
                        <span class="profile__rating-text user__rating-text">публикаци<?= get_noun_plural_form($user_data['post_count'], 'я', 'и', 'й'); ?></span>
                    </p>
                    <p class="profile__rating-item user__rating-item user__rating-item--subscribers">
                        <span class="user__rating-amount"><?= $user_data['follower_count']; ?></span>
                        <span class="profile__rating-text user__rating-text">подписчик<?= get_noun_plural_form($user_data['follower_count'], '', 'а', 'ов'); ?></span>
                    </p>
                </div>
                <div class="profile__user-buttons user__buttons">
                    <?php if ($already_subscribed): ?>
                        <a class="profile__user-button user__button user__button--subscription button button--quartz" href="subscribe.php?user_id=<?= $user_id; ?>">Отписаться</a>
                        <a class="profile__user-button user__button user__button--writing button button--green" href="messages.php?user_id=<?= $user_id; ?>">Сообщение</a>
                    <?php elseif ($auth_user['id'] === $user_id): ?>
                        <div></div>
                    <?php else: ?>
                        <a class="profile__user-button user__button user__button--subscription button button--main" href="subscribe.php?user_id=<?= $user_id; ?>">Подписаться</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="profile__tabs-wrapper tabs">
            <div class="container">
                <div class="profile__tabs filters">
                    <b class="profile__tabs-caption filters__caption">Показать:</b>
                    <ul class="profile__tabs-list filters__list tabs__list">
                        <li class="profile__tabs-item filters__item">
                            <?php $profile_link = 'profile.php?user_id=' . $user_id . '&tab='; // адрес страницы пользователя ?>
                            <a class="profile__tabs-link filters__button tabs__item  button <?= $active_tab === 'posts' ? 'filters__button--active  tabs__item--active' : ''; ?>" href="<?= $profile_link; ?>posts">Посты</a>
                        </li>
                        <li class="profile__tabs-item filters__item">
                            <a class="profile__tabs-link filters__button tabs__item button <?= $active_tab === 'likes' ? 'filters__button--active  tabs__item--active' : ''; ?>" href="<?= $profile_link; ?>likes">Лайки</a>
                        </li>
                        <li class="profile__tabs-item filters__item">
                            <a class="profile__tabs-link filters__button tabs__item button <?= $active_tab === 'following' ? 'filters__button--active  tabs__item--active' : ''; ?>" href="<?= $profile_link; ?>following">Подписки</a>
                        </li>
                    </ul>
                </div>
                <div class="profile__tab-content">
                    <section class="profile__posts tabs__content <?= $active_tab === 'posts' ? 'tabs__content--active' : ''; ?>">
                        <h2 class="visually-hidden">Публикации</h2>
                        <?php foreach ($posts as $post):
                            $type_val = $_SESSION['ct_types'][$post['content_type_id']]['type_val']; // тип публикации
                            $post_link = 'post.php?post_id=' . $post['id']; // формирование ссылки на пост ?>
                            <article class="profile__post post post-<?= $type_val; ?>" id="<?= 'post_id=' . $post['id'] ?>">
                                <header class="post__header">
                                    <?php if ($post['is_repost']): ?>
                                    <div class="post__author">
                                        <a class="post__author-link" href="profile.php?user_id=<?= $repost_author['id']; ?>" title="Автор">
                                            <div class="post__avatar-wrapper post__avatar-wrapper--repost">
                                                <?php if ($repost_author['user_avatar']): ?>
                                                    <img class="post__author-avatar" src="<?= UPLOAD_PATH . $repost_author['user_avatar']; ?>" alt="Аватар пользователя">
                                                <?php else: ?>
                                                    <svg src="img/icon-input-user.svg" width="60" height="60"></svg>
                                                <?php endif; ?>
                                            </div>
                                            <div class="post__info">
                                                <b class="post__author-name">Репост: <?= $repost_author['user_name']; ?></b>
                                                <?php $dt = $repost_author['op_date']; // alias для post date ?>
                                                <time class="post__time" title="<?= get_title_date($dt); ?>" datetime="<?= $dt; ?>"><?= format_date($dt); ?> назад</time>
                                            </div>
                                        </a>
                                    </div>
                                </header>
                                <?php else: ?>
                                    <h2>
                                        <a href="<?= $post_link; ?>">
                                            <?= $post['post_header']; ?>
                                        </a>
                                    </h2>
                                    </header>
                                <?php endif; ?>
                                <div class="post__main">
                                    <?php switch ($type_val):
                                        case 'photo': ?>
                                            <div class="post-photo__image-wrapper">
                                                <img src="<?= UPLOAD_PATH . $post['photo_content']; ?>" alt="Фото от пользователя" width="760" height="396">
                                            </div>
                                            <?php break; ?>

                                        <?php case 'text': ?>
                                            <p>
                                                <?= slice_string($post['text_content'], 'post.php?post_id=' . $post['id']); ?>
                                            </p>
                                            <?php break; ?>

                                        <?php case 'quote': ?>
                                            <blockquote>
                                                <p>
                                                    <?= slice_string($post['text_content'], 'post.php?post_id=' . $post['id']); ?>
                                                </p>
                                                <cite><?= $post['quote_origin'] ?></cite>
                                            </blockquote>
                                            <?php break; ?>

                                        <?php case 'link': ?>
                                            <div class="post-link__wrapper">
                                                <a class="post-link__external" href="<?= $post['link_text_content']; ?>" target="_blank" title="Перейти по ссылке">
                                                    <div class="post-link__icon-wrapper">
                                                        <img src="https://www.google.com/s2/favicons?domain=<?= parse_url($post['link_text_content'], PHP_URL_HOST); ?>" alt="Иконка">
                                                    </div>
                                                    <div class="post-link__info">
                                                        <h3>
                                                            <?= $post['post_header']; ?>
                                                        </h3>
                                                        <span>
                                                <?= $post['link_text_content']; ?>
                                            </span>
                                                    </div>
                                                    <svg class="post-link__arrow" width="11" height="16">
                                                        <use xlink:href="#icon-arrow-right-ad"></use>
                                                    </svg>
                                                </a>
                                            </div>
                                            <?php break; ?>

                                        <?php case 'video': ?>
                                            <div class="post-video__block">
                                                <div class="post-video__preview">
                                                    <?= embed_youtube_cover($post['video_content']); ?>
                                                </div>
                                                <button class="post-video__play-big button" type="button">
                                                    <svg class="post-video__play-big-icon" width="27" height="28">
                                                        <use xlink:href="#icon-video-play-big"></use>
                                                    </svg>
                                                    <span class="visually-hidden">Запустить проигрыватель</span>
                                                </button>
                                            </div>
                                            <?php
                                            break;
                                    endswitch;
                                    ?>
                                </div>
                                <footer class="post__footer">
                                    <div class="post__indicators">
                                        <div class="post__buttons">
                                            <a class="post__indicator post__indicator--likes button" href="like.php?post_id=<?= $post['id']; ?>" title="Лайк">
                                                <svg class="post__indicator-icon" width="20" height="17">
                                                    <use xlink:href="#icon-heart"></use>
                                                </svg>
                                                <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                                                    <use xlink:href="#icon-heart-active"></use>
                                                </svg>
                                                <span>
                                                <?= $post['like_count']; ?>
                                            </span>
                                                <span class="visually-hidden">количество лайков</span>
                                            </a>
                                            <a class="post__indicator post__indicator--repost button" href="repost.php?post_id=<?= $post['id']; ?>" title="Репост">
                                                <svg class="post__indicator-icon" width="19" height="17">
                                                    <use xlink:href="#icon-repost"></use>
                                                </svg>
                                                <span>
                                                <?= $post['repost_count']; ?>
                                            </span>
                                                <span class="visually-hidden">количество репостов</span>
                                            </a>
                                        </div>
                                        <?php $dt = $post['create_dt']; // alias для post date ?>
                                        <time class="post__time" title="<?= get_title_date($dt); ?>" datetime="<?= $dt; ?>"><?= format_date($dt); ?> назад</time>
                                    </div>
                                    <?php if (isset($post['hashtags'])): ?>
                                        <ul class="post__tags">
                                            <?php foreach ($post['hashtags'] as $hashtag): // отображение списка хэштегов ?>
                                                <li>
                                                    <a href="search.php?<?= SEARCH . '=%23' . $hashtag; ?>">
                                                        <?= '#' . $hashtag; ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </footer>
                                <?php if ($show_comments !== $post['id'] and $post['comment_count']): ?>
                                    <div class="comments">
                                        <a class="comments__button button" href="<?= $profile_link . $active_tab . '&show_comments=' . $post['id'] . '#post_id=' . $post['id']; ?>">Показать комментарии</a>
                                    </div>
                                <?php endif; ?>
                                <div class="comments">
                                    <div class="comments__list-wrapper">
                                        <ul class="comments__list">
                                            <?php if ($post['comment_count'] and $show_comments === $post['id']) :
                                            foreach ($comments['comment_list'] as $comment): ?>
                                                <li class="comments__item user">
                                                    <div class="comments__avatar">
                                                        <a class="user__avatar-link" href="profile.php?user_id=<?= $comment['user_id']; ?>">
                                                            <?php if ($comment['user_avatar']): ?>
                                                                <img class="comments__picture" src="<?= UPLOAD_PATH . $comment['user_avatar']; ?>" alt="Аватар пользователя">
                                                            <?php else: ?>
                                                                <svg src="img/icon-input-user.svg" width="60" height="60"></svg>
                                                            <?php endif; ?>
                                                        </a>
                                                    </div>
                                                    <div class="comments__info">
                                                        <div class="comments__name-wrapper">
                                                            <a class="comments__user-name" href="profile.php?user_id=<?= $comment['user_id']; ?>">
                                                        <span>
                                                            <?= $comment['user_name']; ?>
                                                        </span>
                                                            </a>
                                                            <?php $cd = $comment['comment_create_dt']; // alias для даты комментария ?>
                                                            <time class="comments__time" datetime="<?= $cd; ?>"><?= format_date($cd); ?> назад</time>
                                                        </div>
                                                        <p class="comments__text">
                                                            <?= $comment['comment_content']; ?>
                                                        </p>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php if ($post['comment_count'] > $comments['limit']): ?>
                                            <a class="comments__more-link" href="post.php?post_id=<?= $post['id']; ?>&show_all_comments">
                                                <span>Показать все комментарии</span>
                                                <sup class="comments__amount"><?= $post['comment_count']; ?></sup>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                                <form class="comments__form form" action="#" method="post" style="margin-bottom: 0;">
                                    <div class="comments__my-avatar">
                                        <?php if ($auth_user['user_avatar']): ?>
                                            <img class="comments__picture" src="<?= UPLOAD_PATH . $auth_user['user_avatar']; ?>" alt="Аватар пользователя">
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="post-id" value="<?= $post['id'] ?>">
                                    <div class="form__input-section <?= $errors[$post['id']] ? $alert_class : ''; ?>">
                                        <textarea class="comments__textarea form__textarea form__input" name="comment-text" placeholder="Ваш комментарий"><?= $comment_input; ?></textarea>
                                        <label class="visually-hidden">Ваш комментарий</label>
                                        <?php if (isset($errors[$post['id']])): ?>
                                            <?= show_error_msg($errors[$post['id']], 'comment-text'); ?>
                                        <?php endif; ?>
                                    </div>
                                    <button class="comments__submit button button--green" type="submit">Отправить</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </section>

                    <section class="profile__likes tabs__content <?= $active_tab === 'likes' ? 'tabs__content--active' : ''; ?>">
                        <h2 class="visually-hidden">Лайки</h2>
                        <ul class="profile__likes-list">
                            <?php foreach ($likes as $like):
                                $like_ct_type = $_SESSION['ct_types'][$like['content_type_id']]; ?>
                                <li class="post-mini post-mini--photo post user">
                                    <div class="post-mini__user-info user__info">
                                        <div class="post-mini__avatar user__avatar">
                                            <a class="user__avatar-link" href="profile.php?user_id=<?= $like['user_id']; ?>">
                                                <?php if ($like['user_avatar']): ?>
                                                    <img class="post-mini__picture user__picture" src="<?= UPLOAD_PATH . $like['user_avatar']; ?>" alt="Аватар пользователя">
                                                <?php else: ?>
                                                    <svg src="img/icon-input-user.svg" width="60" height="60"></svg>
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                        <div class="post-mini__name-wrapper user__name-wrapper">
                                            <a class="post-mini__name user__name" href="profile.php?user_id=<?= $like['user_id']; ?>">
                                            <span>
                                                <?= $like['user_name']; ?>
                                            </span>
                                            </a>
                                            <div class="post-mini__action">
                                                <span class="post-mini__activity user__additional">Лайкнул вашу публикацию</span>
                                                <?php $ld = $like['like_dt']; // alias для даты лайка ?>
                                                <time class="post-mini__time user__additional" datetime="<?= $ld; ?>"><?= format_date($ld); ?> назад</time>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="post-mini__preview">
                                        <a class="post-mini__link" href="post.php?post_id=<?= $like['post_id']; ?>" title="Перейти на публикацию">
                                            <span class="visually-hidden"><?= $like_ct_type['type_name'] ?></span>
                                            <?php
                                            $type_val = $like_ct_type['type_val']; // добавление данных о типе публикаций
                                            switch ($type_val):
                                                case 'photo': ?>
                                                    <div class="post-mini__image-wrapper">
                                                        <img class="post-mini__image" src="<?= UPLOAD_PATH . $like['photo_content']; ?>" width="109" height="109" alt="Превью публикации">
                                                    </div>
                                                    <?php break; ?>

                                                <?php case 'video': ?>
                                                <div class="post-mini__image-wrapper">
                                                    <?= embed_youtube_cover($like['video_content']); ?>
                                                    <span class="post-mini__play-big">
                                                <svg class="post-mini__play-big-icon" width="12" height="13">
                                                    <use xlink:href="#icon-video-play-big"></use>
                                                </svg>
                                            </span>
                                                </div>
                                                <?php break; ?>

                                            <?php default: ?>
                                                <svg class="post-mini__preview-icon" width="<?= $like_ct_type['type_icon_width']; ?>" height="<?= $like_ct_type['type_icon_height']; ?>">
                                                    <use xlink:href="#icon-filter-<?= $type_val; ?>"></use>
                                                </svg>
                                            <?php endswitch; ?>
                                        </a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>

                    <section class="profile__subscriptions tabs__content <?= $active_tab === 'following' ? 'tabs__content--active' : ''; ?>">
                        <h2 class="visually-hidden">Подписки</h2>
                        <ul class="profile__subscriptions-list">
                            <?php foreach ($followers as $follower): ?>
                                <li class="post-mini post-mini--photo post user">
                                    <div class="post-mini__user-info user__info">
                                        <div class="post-mini__avatar user__avatar">
                                            <a class="user__avatar-link" href="profile.php?user_id=<?= $follower['user_id']; ?>">
                                                <?php if ($follower['user_avatar']): ?>
                                                    <img class="post-mini__picture user__picture" src="<?= UPLOAD_PATH . $follower['user_avatar']; ?>" alt="Аватар пользователя">
                                                <?php else: ?>
                                                    <svg src="img/icon-input-user.svg" width="60" height="60"></svg>
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                        <div class="post-mini__name-wrapper user__name-wrapper">
                                            <a class="post-mini__name user__name" href="profile.php?user_id=<?= $follower['user_id']; ?>">
                                                <span><?= $follower['user_name']; ?></span>
                                            </a>
                                            <time class="post-mini__time user__additional" datetime="<?= $follower['user_reg_dt']; ?>"><?= format_date($user_data['user_reg_dt']); ?> на сайте</time>
                                        </div>
                                    </div>
                                    <div class="post-mini__rating user__rating">
                                        <p class="post-mini__rating-item user__rating-item user__rating-item--publications">
                                            <span class="post-mini__rating-amount user__rating-amount"><?= $follower['post_count']; ?></span>
                                            <span class="post-mini__rating-text user__rating-text">публикаци<?= get_noun_plural_form($follower['post_count'], 'я', 'и', 'й'); ?></span>
                                        </p>
                                        <p class="post-mini__rating-item user__rating-item user__rating-item--subscribers">
                                            <span class="post-mini__rating-amount user__rating-amount"><?= $follower['follower_count']; ?></span>
                                            <span class="post-mini__rating-text user__rating-text">подписчик<?= get_noun_plural_form($follower['follower_count'], '', 'а', 'ов'); ?></span>
                                        </p>
                                    </div>
                                    <div class="post-mini__user-buttons user__buttons">
                                        <?php if ($follower['user_id'] === $auth_user['id']): ?>
                                            <div class="post-mini__user-button"></div>
                                        <?php elseif ($follower['subscribed_to_follower']): ?>
                                            <a class="post-mini__user-button user__button user__button--subscription button button--quartz" href="subscribe.php?user_id=<?=$follower['user_id']; ?>">Отписаться</a>
                                        <?php else: ?>
                                            <a class="post-mini__user-button user__button user__button--subscription button button--main" href="subscribe.php?user_id=<?=$follower['user_id']; ?>">Подписаться</a>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                </div>
            </div>
        </div>
    </div>
</main>
