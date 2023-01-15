<div class="adding-post__input-wrapper form__input-wrapper">
    <label class="adding-post__label form__label" for="tags">Теги</label>
    <div class="form__input-section <?= $show_error_class; ?>">
        <input class="adding-post__input form__input" id="tags" type="text" name="tags" value="<?= $tag_values; ?>" placeholder="Введите теги" title="Теги могут состоять из букв, цифр и символа подчёркивания и должны быть разделены пробелами.">
        <?= $err_msg ?? ''; ?>
    </div>
</div>
