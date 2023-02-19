<main class="page__main page__main--adding-post">
    <div class="page__main-section">
        <div class="container">
            <h1 class="page__title page__title--adding-post">Добавить публикацию</h1>
        </div>
        <div class="adding-post container">
            <div class="adding-post__tabs-wrapper tabs">
                <div class="adding-post__tabs filters">
                    <ul class="adding-post__tabs-list filters__list tabs__list">
<?php foreach ($_SESSION['ct_types'] as $content_type): ?>
                        <li class="adding-post__tabs-item filters__item">
    <?php $active_class_suffix = $post_type === $content_type['type_val'] ? '--active' : ''; // alias ?>
                            <a class="adding-post__tabs-link filters__button filters__button--<?= $content_type['type_val']; ?> filters__button<?= $active_class_suffix; ?> tabs__item tabs__item<?= $active_class_suffix; ?> button" href="?post_type=<?= $content_type['type_val']; ?>">
                                <svg class="filters__icon" width="<?= $content_type['type_icon_width']; ?>" height="<?= $content_type['type_icon_height']; ?>">
                                    <use xlink:href="#icon-filter-<?= $content_type['type_val']; ?>"></use>
                                </svg>
                                <span><?= $content_type['type_name']; ?></span>
                            </a>
                        </li>
<?php endforeach; ?>
                    </ul>
                </div>
                <div class="adding-post__tab-content">
                    <section class="adding-post__photo tabs__content tabs__content<?= $post_type === 'photo' ? '--active' : ''; ?>">
                        <h2 class="visually-hidden">Форма добавления фото</h2>
                        <form class="adding-post__form form" action="add.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="post_type" value="photo">
                            <div class="form__text-inputs-wrapper">
                                <div class="form__text-inputs">
                                    <?= $header_field; ?>
                                    <div class="adding-post__input-wrapper form__input-wrapper">
                                        <label class="adding-post__label form__label" for="photo-url">Ссылка из интернета</label>
                                        <div class="form__input-section <?= $errors['photo-url'] ? $alert_class : ''; ?>">
                                            <input class="adding-post__input form__input" id="photo-url" type="text" name="photo-url" value="<?= $post_data['photo-url'] ?? ''; ?>" placeholder="Введите ссылку">
                                            <?= show_error_msg($errors, 'photo-url'); ?>
                                        </div>
                                    </div>
                                    <?= $tag_field; ?>
                                </div>
                                <?= $error_list; ?>
                            </div>
                            <div class="adding-post__input-file-container form__input-container form__input-container--file">
                                <div class="adding-post__input-file-wrapper form__input-file-wrapper">
                                    <label class="adding-post__input-file-button form__input-file-button form__input-file-button--photo button" for="<?= UPLOAD_IMG_NAME; ?>" title="Выберите фото в формате jpg, png или gif">
                                        <span>Выбрать фото</span>
                                        <svg class="adding-post__attach-icon form__attach-icon" width="10" height="20">
                                            <use xlink:href="#icon-attach"></use>
                                        </svg>
                                    </label>
                                    <input class="visually-hidden" id="<?= UPLOAD_IMG_NAME; ?>" type="file" name="<?= UPLOAD_IMG_NAME; ?>">
                                </div>
                            </div>
                            <div class="adding-post__buttons">
                                <button class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                                <a class="adding-post__close" href="#">Закрыть</a>
                            </div>
                        </form>
                    </section>

                    <section class="adding-post__video tabs__content<?= $post_type === 'video' ? '--active' : ''; ?>">
                        <h2 class="visually-hidden">Форма добавления видео</h2>
                        <form class="adding-post__form form" action="add.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="post_type" value="video">
                            <div class="form__text-inputs-wrapper">
                                <div class="form__text-inputs">
                                    <?= $header_field; ?>
                                    <div class="adding-post__input-wrapper form__input-wrapper">
                                        <label class="adding-post__label form__label" for="video-url">Ссылка youtube <span class="form__input-required">*</span></label>
                                        <div class="form__input-section <?= $errors['video-url'] ? $alert_class : ''; ?>">
                                            <input class="adding-post__input form__input" id="video-url" type="text" name="video-url" value="<?= $post_data['video-url'] ?? ''; ?>" placeholder="Введите ссылку">
                                            <?= show_error_msg($errors, 'video-url'); ?>
                                        </div>
                                    </div>
                                    <?= $tag_field; ?>
                                </div>
                                <?= $error_list; ?>
                            </div>

                            <div class="adding-post__buttons">
                                <button class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                                <a class="adding-post__close" href="#">Закрыть</a>
                            </div>
                        </form>
                    </section>

                    <section class="adding-post__text tabs__content<?= $post_type === 'text' ? '--active' : ''; ?>">
                        <h2 class="visually-hidden">Форма добавления текста</h2>
                        <form class="adding-post__form form" action="add.php" method="post">
                            <input type="hidden" name="post_type" value="text">
                            <div class="form__text-inputs-wrapper">
                                <div class="form__text-inputs">
                                    <?= $header_field; ?>
                                    <div class="adding-post__textarea-wrapper form__textarea-wrapper">
                                        <label class="adding-post__label form__label" for="post-text">Текст поста <span class="form__input-required">*</span></label>
                                        <div class="form__input-section <?= $errors['post-text'] ? $alert_class : ''; ?>">
                                            <textarea class="adding-post__textarea form__textarea form__input" id="post-text" name="post-text" placeholder="Введите текст публикации"><?= $post_data['post-text'] ?? ''; ?></textarea>
                                            <?= show_error_msg($errors, 'post-text'); ?>
                                        </div>
                                    </div>
                                    <?= $tag_field; ?>
                                </div>
                                <?= $error_list; ?>
                            </div>
                            <div class="adding-post__buttons">
                                <button class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                                <a class="adding-post__close" href="#">Закрыть</a>
                            </div>
                        </form>
                    </section>

                    <section class="adding-post__quote tabs__content<?= $post_type === 'quote' ? '--active' : ''; ?>">
                        <h2 class="visually-hidden">Форма добавления цитаты</h2>
                        <form class="adding-post__form form" action="add.php" method="post">
                            <input type="hidden" name="post_type" value="quote">
                            <div class="form__text-inputs-wrapper">
                                <div class="form__text-inputs">
                                    <?= $header_field; ?>
                                    <div class="adding-post__input-wrapper form__textarea-wrapper">
                                        <label class="adding-post__label form__label" for="post-text">Текст цитаты <span class="form__input-required">*</span></label>
                                        <div class="form__input-section <?= $errors['post-text'] ? $alert_class : ''; ?>">
                                            <textarea class="adding-post__textarea adding-post__textarea--quote form__textarea form__input" id="post-text" name="post-text" placeholder="Текст цитаты"><?= $post_data['post-text'] ?? ''; ?></textarea>
                                            <?= show_error_msg($errors, 'post-text'); ?>
                                        </div>
                                    </div>
                                    <div class="adding-post__textarea-wrapper form__input-wrapper">
                                        <label class="adding-post__label form__label" for="quote-author">Автор <span class="form__input-required">*</span></label>
                                        <div class="form__input-section <?= $errors['quote-author'] ? $alert_class : ''; ?>">
                                            <input class="adding-post__input form__input" id="quote-author" type="text" name="quote-author" value="<?= $post_data['quote-author'] ?? ''; ?>" placeholder="Автор цитаты">
                                            <?= show_error_msg($errors, 'quote-author'); ?>
                                        </div>
                                    </div>
                                    <?= $tag_field; ?>
                                </div>
                                <?= $error_list; ?>
                            </div>
                            <div class="adding-post__buttons">
                                <button class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                                <a class="adding-post__close" href="#">Закрыть</a>
                            </div>
                        </form>
                    </section>

                    <section class="adding-post__link tabs__content<?= $post_type === 'link' ? '--active' : ''; ?>">
                        <h2 class="visually-hidden">Форма добавления ссылки</h2>
                        <form class="adding-post__form form" action="add.php" method="post">
                            <input type="hidden" name="post_type" value="link">
                            <div class="form__text-inputs-wrapper">
                                <div class="form__text-inputs">
                                    <?= $header_field; ?>
                                    <div class="adding-post__textarea-wrapper form__input-wrapper">
                                        <label class="adding-post__label form__label" for="post-link">Ссылка <span class="form__input-required">*</span></label>
                                        <div class="form__input-section <?= $errors['post-link'] ? $alert_class : ''; ?>">
                                            <input class="adding-post__input form__input" id="post-link" type="text" name="post-link" value="<?= $post_data['post-link'] ?? ''; ?>" placeholder="Введите ссылку">
                                            <?= show_error_msg($errors, 'post-link'); ?>
                                        </div>
                                    </div>
                                    <?= $tag_field; ?>
                                </div>
                                <?= $error_list; ?>
                            </div>
                            <div class="adding-post__buttons">
                                <button class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                                <a class="adding-post__close" href="#">Закрыть</a>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal modal--adding">
    <div class="modal__wrapper">
        <button class="modal__close-button button" type="button">
            <svg class="modal__close-icon" width="18" height="18">
                <use xlink:href="#icon-close"></use>
            </svg>
            <span class="visually-hidden">Закрыть модальное окно</span></button>
        <div class="modal__content">
            <h1 class="modal__title">Пост добавлен</h1>
            <p class="modal__desc">
                Озеро Байкал – огромное древнее озеро в горах Сибири к северу от монгольской границы. Байкал считается самым глубоким озером в мире. Он окружен сефтью пешеходных маршрутов, называемых Большой байкальской тропой. Деревня Листвянка, расположенная на западном берегу озера, – популярная отправная точка для летних экскурсий.
            </p>
            <div class="modal__buttons">
                <a class="modal__button button button--main" href="#">Синяя кнопка</a>
                <a class="modal__button button button--gray" href="#">Серая кнопка</a>
            </div>
        </div>
    </div>
</div>
