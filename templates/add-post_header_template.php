<div class="adding-post__input-wrapper form__input-wrapper">
    <label class="adding-post__label form__label" for="<?= $type; ?>-heading">Заголовок <span class="form__input-required">*</span></label>
    <div class="form__input-section <?= $errors[$type . '-heading'] ? 'form__input-section--error' : ''; ?>">
        <input class="adding-post__input form__input" id="<?= $type; ?>-heading" type="text" name="<?= $type; ?>-heading" value="<?= getPostVal($type .'-heading'); ?>" placeholder="Введите заголовок">
        <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
        <div class="form__error-text">
            <h3 class="form__error-title">Пустой заголовок</h3>
            <p class="form__error-desc">Придумайте заголовок для вашего поста.</p>
        </div>
    </div>
</div>
