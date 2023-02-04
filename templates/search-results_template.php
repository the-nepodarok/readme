<main class="page__main page__main--search-results">
    <h1 class="visually-hidden">Страница результатов поиска</h1>
    <section class="search">
        <h2 class="visually-hidden">Результаты поиска</h2>
        <div class="search__query-wrapper">
            <div class="search__query container">
                <span>Вы искали:</span>
                <span class="search__query-text"><?= $search_query; ?></span>
            </div>
        </div>
        <div class="search__results-wrapper">
            <div class="container">
                <div class="search__content">
    <?php foreach ($posts as $post):
              $type_val = $_SESSION['ct_types'][$post['content_type_id']]['type_val']; // добавление данных о типе публикаций
              $post_link = 'post.php?post_id=' . $post['id']; // формирование ссылки на пост ?>
                    <article class="search__post post post-<?= $type_val; ?>">
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
                    <?php switch ($type_val):
                        case 'photo': ?>
                            <h2>
                                <a href="<?= $post_link; ?>">
                                    <?= $post['post_header']; ?>
                                </a>
                            </h2>
                            <div class="post-photo__image-wrapper">
                                <img src="<?= UPLOAD_PATH . $post['photo_content']; ?>" alt="Фото от пользователя" width="760" height="396">
                            </div>
                        </div>
                        <?php break; ?>

                        <?php case 'text': ?>
                        <h2>
                            <a href="<?= $post_link; ?>">
                                <?= $post['post_header']; ?>
                            </a>
                        </h2>
                        <p>
                            <?= slice_string($post['text_content'], 'post.php?post_id=' . $post['id']); ?>
                        </p>
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
                    <?php
                              break;
                          endswitch;
                    ?>
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
                                <a class="post__indicator post__indicator--comments button" href="<?= $post_link; ?>" title="Комментарии">
                                    <svg class="post__indicator-icon" width="19" height="17">
                                        <use xlink:href="#icon-comment"></use>
                                    </svg>
                                    <span><?= $post['comment_count']; ?></span>
                                    <span class="visually-hidden">количество комментариев</span>
                                </a>
                            </div>
                        </footer>
                    </article>
    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</main>
