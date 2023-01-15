<div class="adding-post__input-wrapper form__input-wrapper">
    <label class="adding-post__label form__label" for="post-heading">Заголовок <span class="form__input-required">*</span></label>
    <div class="form__input-section <?= $show_error_class; ?>">
        <input class="adding-post__input form__input" id="post-heading" type="text" name="post-heading" value="<?= $post_heading; ?>" placeholder="Введите заголовок">
        <?= $err_msg; ?>
    </div>
</div>
