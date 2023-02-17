<?php $user = $_SESSION['user']; ?>
<main class="page__main page__main--messages">
    <h1 class="visually-hidden">Личные сообщения</h1>
    <section class="messages tabs">
        <h2 class="visually-hidden">Сообщения</h2>
        <div class="messages__contacts">
            <ul class="messages__contacts-list tabs__list">
                <?php foreach ($dialogues as $dialogue): ?>
                    <li class="messages__contacts-item">
                        <?php $is_active_dialogue = ($user_id === $dialogue['id']); ?>
                        <a class="messages__contacts-tab tabs__item <?= $is_active_dialogue ? 'messages__contacts-tab--active tabs__item--active' : ''; ?>" href="messages.php?user_id=<?= $dialogue['id']; ?>">
                            <div class="messages__avatar-wrapper">
                                <?php if ($dialogue['user_avatar']): ?>
                                    <img class="messages__avatar" src="<?= UPLOAD_PATH . $dialogue['user_avatar']; ?>" alt="Аватар пользователя">
                                <?php endif; ?>

                                <?php if ($dialogue['unread_counter'] ?? 0): ?>
                                    <i class="messages__indicator">
                                        <?= $dialogue['unread_counter']; ?>
                                    </i>
                                <?php endif; ?>
                            </div>
                            <div class="messages__info">
                            <span class="messages__contact-name">
                                <?= $dialogue['user_name']; ?>
                            </span>
                                <div class="messages__preview">
                                    <p class="messages__preview-text">
                                        <?= $dialogue['message_content'] ?? ''; ?>
                                    </p>
                                    <?php $last_msg_dt = $dialogue['message_create_dt'] ?? ''; ?>
                                    <time class="messages__preview-time" title="<?= get_title_date($last_msg_dt); ?>" datetime="<?= $last_msg_dt; ?>">
                                        <?= $dialogue['format_date'] ?? ''; ?>
                                    </time>
                                </div>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if ($user_id): ?>
            <div class="messages__chat">
                <div class="messages__chat-wrapper">
                    <ul class="messages__list tabs__content tabs__content--active">
                        <?php foreach ($messages as $message): ?>
                            <li class="messages__item <?= $message['message_sender_id'] == $user['id'] ? 'messages__item--my' : ''; ?>">
                                <div class="messages__info-wrapper">
                                    <div class="messages__item-avatar">
                                        <a class="messages__author-link" href="profile.php?user_id=<?= $message['message_sender_id']; ?>">
                                            <?php if ($message['message_sender_id'] === $user['id']):
                                                if ($user['user_avatar']): ?>
                                                    <img class="messages__avatar" src="<?= UPLOAD_PATH . $user['user_avatar']; ?>" alt="Аватар пользователя">
                                                <?php else: ?>
                                                    <svg src="img/icon-input-user.svg" width="60" height="60"></svg>
                                                <?php endif; ?>
                                            <?php else:
                                                if ($current_dialogue['user_avatar']): ?>
                                                    <img class="messages__avatar" src="<?= UPLOAD_PATH . $current_dialogue['user_avatar']; ?>" alt="Аватар пользователя">
                                                <?php else: ?>
                                                    <svg src="img/icon-input-user.svg" width="60" height="60"></svg>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                    <div class="messages__item-info">
                                        <a class="messages__author" href="profile.php?user_id=<?= $message['message_sender_id']; ?>">
                                            <?php if ($message['message_sender_id'] === $user['id']): ?>
                                                <?= $user['user_name']; ?>
                                            <?php else: ?>
                                                <?= $current_dialogue['user_name']; ?>
                                            <?php endif; ?>
                                        </a>
                                        <?php $msg_dt = $message['message_create_dt']; ?>
                                        <time class="messages__time" title="<?= get_title_date($msg_dt); ?>" datetime="<?= $msg_dt; ?>">
                                            <?= format_date($msg_dt); ?> назад
                                        </time>
                                    </div>
                                </div>
                                <p class="messages__text">
                                    <?= $message['message_content']; ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                </div>
                <div class="comments">
                    <form class="comments__form form" action="#" method="post">
                        <div class="comments__my-avatar">
                            <?php if ($user['user_avatar']): ?>
                                <img class="comments__picture" src="<?= UPLOAD_PATH . $user['user_avatar']; ?>" alt="Аватар пользователя">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="receiver-id" value="<?= $user_id; ?>">
                        <div class="form__input-section <?= $errors ? $alert_class : ''; ?>">
                            <textarea class="comments__textarea form__textarea form__input" name="message-text" placeholder="Ваше сообщение"><?= $message_input; ?></textarea>
                            <label class="visually-hidden">Ваше сообщение</label>
                            <?= show_error_msg($errors, 'message-text'); ?>
                        </div>
                        <button class="comments__submit button button--green" type="submit">Отправить</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="messages__chat" style="min-height: 680px;"></div>
        <?php endif; ?>
    </section>
</main>
