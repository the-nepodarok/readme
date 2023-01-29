<main class="page__main page__main--registration">
    <div class="container">
        <h1 class="page__title page__title--registration">Регистрация</h1>
    </div>
    <section class="registration container">
        <h2 class="visually-hidden">Форма регистрации</h2>
        <form class="registration__form form" action="#" method="post" enctype="multipart/form-data">
            <div class="form__text-inputs-wrapper">
                <div class="form__text-inputs">
                    <div class="registration__input-wrapper form__input-wrapper">
                        <label class="registration__label form__label" for="registration-email">Электронная почта <span class="form__input-required">*</span></label>
                        <div class="form__input-section <?= $errors['email'] ? $alert_class : ''; ?>">
                            <input class="registration__input form__input" id="registration-email" type="email" name="email" placeholder="Укажите эл.почту" value="<?= $reg_data['email'] ?? ''; ?>">
                            <?= show_error_msg($errors, 'email'); ?>
                        </div>
                    </div>
                    <div class="registration__input-wrapper form__input-wrapper">
                        <label class="registration__label form__label" for="registration-login">Логин <span class="form__input-required">*</span></label>
                        <div class="form__input-section <?= $errors['login'] ? $alert_class : ''; ?>">
                            <input class="registration__input form__input" id="registration-login" type="text" name="login" placeholder="Укажите логин" value="<?= $reg_data['login'] ?? ''; ?>">
                            <?= show_error_msg($errors, 'login'); ?>
                        </div>
                    </div>
                    <div class="registration__input-wrapper form__input-wrapper">
                        <label class="registration__label form__label" for="registration-password">Пароль<span class="form__input-required">*</span></label>
                        <div class="form__input-section <?= $errors['password'] ? $alert_class : ''; ?>">
                            <input class="registration__input form__input" id="registration-password" type="password" name="password" placeholder="Придумайте пароль" value="<?= $reg_data['password'] ?? ''; ?>">
                            <?= show_error_msg($errors, 'password'); ?>
                        </div>
                    </div>
                    <div class="registration__input-wrapper form__input-wrapper">
                        <label class="registration__label form__label" for="registration-password-repeat">Повтор пароля<span class="form__input-required">*</span></label>
                        <div class="form__input-section <?= $errors['password-repeat'] ? $alert_class : ''; ?>">
                            <input class="registration__input form__input" id="registration-password-repeat" type="password" name="password-repeat" placeholder="Повторите пароль" value="<?= $reg_data['password-repeat'] ?? ''; ?>">
                            <?= show_error_msg($errors, 'password-repeat'); ?>
                        </div>
                    </div>
                </div>
                <?= $error_list; ?>
            </div>
            <div class="registration__input-file-container form__input-container form__input-container--file">
                <div class="registration__input-file-wrapper form__input-file-wrapper">
                    <label class="registration__input-file-button form__input-file-button button" for="<?= NEW_USER_IMG_NAME; ?>" title="Выберите фото в формате jpg, png или gif">
                        <span>Выбрать фото</span>
                        <svg class="registration__attach-icon form__attach-icon" width="10" height="20">
                            <use xlink:href="#icon-attach"></use>
                        </svg>
                    </label>
                    <input class="visually-hidden" id="<?= NEW_USER_IMG_NAME; ?>" type="file" name="<?= NEW_USER_IMG_NAME; ?>">
                </div>
            </div>
            <button class="registration__submit button button--main" type="submit">Отправить</button>
        </form>
    </section>
</main>
