<section class="page__main page__main--popular">
    <div class="container">
        <h1 class="page__title page__title--popular">Популярное</h1>
    </div>
    <div class="popular container">
        <div class="popular__filters-wrapper">
            <div class="popular__sorting sorting">
                <b class="popular__sorting-caption sorting__caption">Сортировка:</b>
                <ul class="popular__sorting-list sorting__list">
                    <li class="sorting__item sorting__item--popular">
                        <a class="sorting__link sorting__link--active" href="#">
                            <span>Популярность</span>
                            <svg class="sorting__icon" width="10" height="12">
                                <use xlink:href="#icon-sort"></use>
                            </svg>
                        </a>
                    </li>
                    <li class="sorting__item">
                        <a class="sorting__link" href="#">
                            <span>Лайки</span>
                            <svg class="sorting__icon" width="10" height="12">
                                <use xlink:href="#icon-sort"></use>
                            </svg>
                        </a>
                    </li>
                    <li class="sorting__item">
                        <a class="sorting__link" href="#">
                            <span>Дата</span>
                            <svg class="sorting__icon" width="10" height="12">
                                <use xlink:href="#icon-sort"></use>
                            </svg>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="popular__filters filters">
                <b class="popular__filters-caption filters__caption">Тип контента:</b>
                <ul class="popular__filters-list filters__list">
                    <li class="popular__filters-item popular__filters-item--all filters__item filters__item--all">
                        <a class="filters__button filters__button--ellipse filters__button--all filters__button--active" href="#">
                            <span>Все</span>
                        </a>
                    </li>
                    <li class="popular__filters-item filters__item">
                        <a class="filters__button filters__button--photo button" href="#">
                            <span class="visually-hidden">Фото</span>
                            <svg class="filters__icon" width="22" height="18">
                                <use xlink:href="#icon-filter-photo"></use>
                            </svg>
                        </a>
                    </li>
                    <li class="popular__filters-item filters__item">
                        <a class="filters__button filters__button--video button" href="#">
                            <span class="visually-hidden">Видео</span>
                            <svg class="filters__icon" width="24" height="16">
                                <use xlink:href="#icon-filter-video"></use>
                            </svg>
                        </a>
                    </li>
                    <li class="popular__filters-item filters__item">
                        <a class="filters__button filters__button--text button" href="#">
                            <span class="visually-hidden">Текст</span>
                            <svg class="filters__icon" width="20" height="21">
                                <use xlink:href="#icon-filter-text"></use>
                            </svg>
                        </a>
                    </li>
                    <li class="popular__filters-item filters__item">
                        <a class="filters__button filters__button--quote button" href="#">
                            <span class="visually-hidden">Цитата</span>
                            <svg class="filters__icon" width="21" height="20">
                                <use xlink:href="#icon-filter-quote"></use>
                            </svg>
                        </a>
                    </li>
                    <li class="popular__filters-item filters__item">
                        <a class="filters__button filters__button--link button" href="#">
                            <span class="visually-hidden">Ссылка</span>
                            <svg class="filters__icon" width="21" height="18">
                                <use xlink:href="#icon-filter-link"></use>
                            </svg>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="popular__posts">
            <?php if (isset($posts)): ?>
            <?php foreach ($posts as $post): ?>
            <article class="popular__post post <?= $post['post_type']; ?>">
                <header class="post__header">
                    <h2>
                        <!--здесь заголовок-->
                        <?= $post['post_title']; ?>
                    </h2>
                </header>
                <div class="post__main">
                    <?php if (isset($post['post_content'])): // Проверка на наличие у поста содержимого | the-nepodarok ?>
                    <?php switch ($post['post_type']):
                          case 'post-quote': ?>
                    <!--содержимое для поста-цитаты-->
                    <blockquote>
                        <p>
                            <!--здесь текст-->
                            <?= $post['post_content']; ?>
                        </p>
                        <cite>Неизвестный Автор</cite>
                    </blockquote>
                    <?php break; ?>

                    <?php case 'post-text': ?>
                    <!--содержимое для поста-текста-->
                    <p>
                        <!--здесь текст-->
                        <?= $post['post_content']; ?>
                    </p>
                    <?php break; ?>

                    <?php case 'post-photo': ?>
                    <!--содержимое для поста-фото-->
                    <div class="post-photo__image-wrapper">
                        <img src="img/<?= $post['post_content']; ?>" alt="Фото от пользователя" width="360" height="240">
                    </div>
                    <?php break; ?>

                    <?php case 'post-link': ?>
                    <!--содержимое для поста-ссылки-->
                    <div class="post-link__wrapper">
                        <a class="post-link__external" href="http://<?= $post['post_content']; ?>" title="Перейти по ссылке">
                            <div class="post-link__info-wrapper">
                                <div class="post-link__icon-wrapper">
                                    <img src="https://www.google.com/s2/favicons?domain=vitadental.ru" alt="Иконка">
                                </div>
                                <div class="post-link__info">
                                    <h3>
                                        <!--здесь заголовок-->
                                        <?= $post['post_title']; ?>
                                    </h3>
                                </div>
                            </div>
                            <span>
                                <!--здесь ссылка-->
                                <?= $post['post_content']; ?>
                            </span>
                        </a>
                    </div>
                    <?php break; ?>

                    <?php case 'post-video': ?>
                    <!--содержимое для поста-видео-->
                    <div class="post-video__block">
                        <div class="post-video__preview">
                            <?= embed_youtube_cover(
                                /* вставьте ссылку на видео */
                                 $post['post_content']
                            ); ?>
                            <img src="img/coast-medium.jpg" alt="Превью к видео" width="360" height="188">
                        </div>
                        <a href="post-details.html" class="post-video__play-big button">
                            <svg class="post-video__play-big-icon" width="14" height="14">
                                <use xlink:href="#icon-video-play-big"></use>
                            </svg>
                            <span class="visually-hidden">Запустить проигрыватель</span>
                        </a>
                    </div>
                    <?php
                                break;
                            endswitch;
                        endif;
                    ?>
                </div>
                <footer class="post__footer">
                    <div class="post__author">
                        <a class="post__author-link" href="#" title="Автор">
                            <div class="post__avatar-wrapper">
                                <!--укажите путь к файлу аватара-->
                                <img class="post__author-avatar" src="img/<?= $post['post_avatar']; ?>" alt="Аватар пользователя">
                            </div>
                            <div class="post__info">
                                <b class="post__author-name">
                                    <!--здесь имя пользоателя-->
                                    <?= $post['post_user']; ?>
                                </b>
                                <time class="post__time" title="<?= date('d.m.Y H:i', strtotime($post['date'])); ?>" datetime="<?= $post['date']; ?>"><?= formatDate($post['date']); ?></time>
                            </div>
                        </a>
                    </div>
                    <div class="post__indicators">
                        <div class="post__buttons">
                            <a class="post__indicator post__indicator--likes button" href="#" title="Лайк">
                                <svg class="post__indicator-icon" width="20" height="17">
                                    <use xlink:href="#icon-heart"></use>
                                </svg>
                                <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                                    <use xlink:href="#icon-heart-active"></use>
                                </svg>
                                <span>0</span>
                                <span class="visually-hidden">количество лайков</span>
                            </a>
                            <a class="post__indicator post__indicator--comments button" href="#" title="Комментарии">
                                <svg class="post__indicator-icon" width="19" height="17">
                                    <use xlink:href="#icon-comment"></use>
                                </svg>
                                <span>0</span>
                                <span class="visually-hidden">количество комментариев</span>
                            </a>
                        </div>
                    </div>
                </footer>
            </article>
            <?php
                    endforeach;
                endif;
            ?>
        </div>
    </div>
</section>