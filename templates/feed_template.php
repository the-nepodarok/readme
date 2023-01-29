<main class="page__main page__main--feed">
    <div class="container">
        <h1 class="page__title page__title--feed">Моя лента</h1>
    </div>
    <div class="page__main-wrapper container">
        <section class="feed">
            <h2 class="visually-hidden">Лента</h2>
            <div class="feed__main-wrapper">
                <div class="feed__wrapper">
    <?php if ($posts):
              foreach ($posts as $post): ?>
                    <article class="feed__post post post-<?= $post['type_val']; ?>">
                        <header class="post__header post__author">
                            <a class="post__author-link" href="#" title="Автор">
                                <div class="post__avatar-wrapper">
                    <?php if ($post['user_avatar']) : ?>
                                    <img class="post__author-avatar" src="<?= UPLOAD_PATH . $post['user_avatar']; ?>" alt="Аватар пользователя" width="60" height="60">
                    <?php endif; ?>
                                </div>
                                <div class="post__info">
                                    <b class="post__author-name"><?= $post['user_name']; ?></b>
                  <?php $dt = $post['create_dt']; // alias для post date ?>
                                    <time class="post__time" title="<?= get_title_date($dt); ?>" datetime="<?= $dt; ?>"><?= format_date($dt); ?> назад</time>
                                </div>
                            </a>
                        </header>
                        <div class="post__main">
                  <?php switch ($post['type_val']):
                      case 'photo': ?>
                            <h2>
                                <a href="<?= $post_link . $post['id']; ?>">
                                    <?= $post['post_header']; ?>
                                </a>
                            </h2>
                            <div class="post-photo__image-wrapper">
                                <img src="<?= UPLOAD_PATH . $post['photo_content']; ?>" alt="Фото от пользователя" width="760" height="396">
                            </div>
                      <?php break; ?>

                      <?php case 'text': ?>
                            <h2>
                                <a href="<?= $post_link . $post['id']; ?>">
                                    <?= $post['post_header']; ?>
                                </a>
                            </h2>
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
                        <footer class="post__footer post__indicators">
                            <div class="post__buttons">
                                <a class="post__indicator post__indicator--likes button" href="#" title="Лайк">
                                    <svg class="post__indicator-icon" width="20" height="17">
                                        <use xlink:href="#icon-heart"></use>
                                    </svg>
                                    <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                                        <use xlink:href="#icon-heart-active"></use>
                                    </svg>
                                    <span><?= $post['like_count']; ?></span>
                                    <span class="visually-hidden">количество лайков</span>
                                </a>
                                <a class="post__indicator post__indicator--comments button" href="<?= $post_link . $post['id']; ?>" title="Комментарии">
                                    <svg class="post__indicator-icon" width="19" height="17">
                                        <use xlink:href="#icon-comment"></use>
                                    </svg>
                                    <span><?= $post['comment_count']; ?></span>
                                    <span class="visually-hidden">количество комментариев</span>
                                </a>
                                <a class="post__indicator post__indicator--repost button" href="?repost_id=<?= $post['id']; ?>" title="Репост">
                                    <svg class="post__indicator-icon" width="19" height="17">
                                        <use xlink:href="#icon-repost"></use>
                                    </svg>
                                    <span><?= $post['repost_count']; ?></span>
                                    <span class="visually-hidden">количество репостов</span>
                                </a>
                            </div>
                  <?php if (isset($post['hashtags'])): ?>
                            <ul class="post__tags">
                        <?php foreach ($post['hashtags'] as $hashtag): // отображение списка хэштегов ?>
                                <li>
                                    <a href="#">
                                        <?= '#' . $hashtag; ?>
                                    </a>
                                </li>
                        <?php endforeach; ?>
                            </ul>
                  <?php endif; ?>
                        </footer>
                    </article>
    <?php
              endforeach;
          endif;
    ?>
                </div>
            </div>
            <ul class="feed__filters filters">
                <li class="feed__filters-item filters__item">
                    <a class="filters__button <?= $type_id === 0 ? 'filters__button--active' : ''; ?>" href="?">
                        <span>Все</span>
                    </a>
                </li>
    <?php foreach ($_SESSION['ct_types'] as $type): ?>
                <li class="feed__filters-item filters__item">
                    <a class="filters__button filters__button--<?= $type['type_val']; ?> <?= $type_id === $type['id'] ? 'filters__button--active' : ''; ?> button" href="?type_id=<?= $type['id']; ?>">
                        <span class="visually-hidden"><?= $type['type_name']; ?></span>
                        <svg class="filters__icon" width="<?= $type['type_icon_width']; ?>" height="<?= $type['type_icon_height']; ?>">
                            <use xlink:href="#icon-filter-<?= $type['type_val']; ?>"></use>
                        </svg>
                    </a>
                </li>
    <?php endforeach; ?>
            </ul>
        </section>
        <aside class="promo">
            <article class="promo__block promo__block--barbershop">
                <h2 class="visually-hidden">Рекламный блок</h2>
                <p class="promo__text">
                    Все еще сидишь на окладе в офисе? Открой свой барбершоп по нашей франшизе!
                </p>
                <a class="promo__link" href="#">
                    Подробнее
                </a>
            </article>
            <article class="promo__block promo__block--technomart">
                <h2 class="visually-hidden">Рекламный блок</h2>
                <p class="promo__text">
                    Товары будущего уже сегодня в онлайн-сторе Техномарт!
                </p>
                <a class="promo__link" href="#">
                    Перейти в магазин
                </a>
            </article>
            <article class="promo__block">
                <h2 class="visually-hidden">Рекламный блок</h2>
                <p class="promo__text">
                    Здесь<br> могла быть<br> ваша реклама
                </p>
                <a class="promo__link" href="#">
                    Разместить
                </a>
            </article>
        </aside>
    </div>
</main>
