<main class="page__main page__main--publication">
  <div class="container">
    <h1 class="page__title page__title--publication"><?= $post['post_header']; ?></h1>
    <section class="post-details">
      <h2 class="visually-hidden">Публикация</h2>
      <div class="post-details__wrapper post-<?= $post['type_val']; ?>">
        <div class="post-details__main-block post post--details">
          <?=$post_type_template; ?>
          <div class="post__indicators">
            <div class="post__buttons">
              <a class="post__indicator post__indicator--likes button" href="#" title="Лайк">
                <svg class="post__indicator-icon" width="20" height="17">
                  <use xlink:href="#icon-heart"></use>
                </svg>
                <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                  <use xlink:href="#icon-heart-active"></use>
                </svg>
                <span><?= $count_arr['like_count']; ?></span>
                <span class="visually-hidden">количество лайков</span>
              </a>
              <a class="post__indicator post__indicator--comments button" href="#" title="Комментарии">
                <svg class="post__indicator-icon" width="19" height="17">
                  <use xlink:href="#icon-comment"></use>
                </svg>
                <span><?= $count_arr['comment_count']; ?></span>
                <span class="visually-hidden">количество комментариев</span>
              </a>
              <a class="post__indicator post__indicator--repost button" href="?repost_id=<?= $post['id']; ?>" title="Репост">
                <svg class="post__indicator-icon" width="19" height="17">
                  <use xlink:href="#icon-repost"></use>
                </svg>
                <span><?= $count_arr['repost_count']; ?></span>
                <span class="visually-hidden">количество репостов</span>
              </a>
            </div>
            <?php $vc = $post['view_count']; // alias для числа просмотров ?>
            <span class="post__view"><?= $vc . ' просмотр' . get_noun_plural_form($vc, '', 'а', 'ов'); ?></span>
          </div>
          <ul class="post__tags">
            <?php foreach ($post_hashtag_list as $hashtag): // отображение списка хэштегов ?>
            <li><a href="#"><?= '#' . $hashtag; ?></a></li>
            <?php endforeach; ?>
          </ul>
          <div class="comments">
            <form class="comments__form form" action="#" method="post">
              <div class="comments__my-avatar">
                <?php if ($_SESSION['user']['user_avatar']): ?>
                <img class="comments__picture" src="<?= UPLOAD_PATH . $_SESSION['user']['user_avatar']; ?>" alt="Аватар пользователя">
                <?php endif; ?>
              </div>
              <div class="form__input-section _form__input-section--error"> <!-- не забыть вернуть -->
                <textarea class="comments__textarea form__textarea form__input" placeholder="Ваш комментарий"></textarea>
                <label class="visually-hidden">Ваш комментарий</label>
                <button class="form__error-button button" type="button">!</button>
                <div class="form__error-text">
                  <h3 class="form__error-title">Ошибка валидации</h3>
                  <p class="form__error-desc">Это поле обязательно к заполнению</p>
                </div>
              </div>
              <button class="comments__submit button button--green" type="submit">Отправить</button>
            </form>
            <div class="comments__list-wrapper">
              <ul class="comments__list">
              <?php foreach ($comment_list as $comment): ?>
                <li class="comments__item user">
                  <div class="comments__avatar">
                    <a class="user__avatar-link" href="#">
                      <?php if ($comment['user_avatar']): ?>
                      <img class="comments__picture" src="<?= UPLOAD_PATH . $comment['user_avatar']; ?>" alt="Аватар пользователя">
                      <?php endif; ?>
                    </a>
                  </div>
                  <div class="comments__info">
                    <div class="comments__name-wrapper">
                      <a class="comments__user-name" href="#">
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
              <?php if ($hide_comments): ?>
              <a class="comments__more-link" href="?<?= 'post_id=' . $post['id']; ?>&show_all_comments">
                <span>Показать все комментарии</span>
                <sup class="comments__amount"><?= $count_arr['comment_count']; ?></sup>
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="post-details__user user">
          <div class="post-details__user-info user__info">
            <div class="post-details__avatar user__avatar">
              <a class="post-details__avatar-link user__avatar-link" href="#">
                <?php if ($post['user_avatar']): ?>
                <img class="post-details__picture user__picture" src="<?= UPLOAD_PATH . $post['user_avatar']; ?>" alt="Аватар пользователя">
                <?php endif; ?>
              </a>
            </div>
            <div class="post-details__name-wrapper user__name-wrapper">
              <a class="post-details__name user__name" href="#">
                <span><?= $post['user_name']; ?></span>
              </a>
              <time class="post-details__time user__time" datetime="<?= $post['reg_date']; ?>"><?= format_date($post['reg_date']); ?> на сайте</time>
            </div>
          </div>
          <div class="post-details__rating user__rating">
            <p class="post-details__rating-item user__rating-item user__rating-item--subscribers">
              <span class="post-details__rating-amount user__rating-amount"><?= $count_arr['follower_count']; ?></span>
              <span class="post-details__rating-text user__rating-text">подписчик<?= $count_arr['follower_count'] ? get_noun_plural_form($count_arr['follower_count'], '', 'а', 'ов') : 'ов'; ?></span>
            </p>
            <p class="post-details__rating-item user__rating-item user__rating-item--publications">
              <span class="post-details__rating-amount user__rating-amount"><?= $count_arr['post_count']; ?></span>
              <span class="post-details__rating-text user__rating-text">публикаци<?= get_noun_plural_form($count_arr['post_count'], 'я', 'и', 'й'); ?></span>
            </p>
          </div>
          <div class="post-details__user-buttons user__buttons">
            <button class="user__button user__button--subscription button button--main" type="button">Подписаться</button>
            <a class="user__button user__button--writing button button--green" href="#">Сообщение</a>
          </div>
        </div>
      </div>
    </section>
  </div>
</main>
