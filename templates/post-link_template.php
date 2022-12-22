<div class="post__main">
    <div class="post-link__wrapper">
        <a class="post-link__external" href="https://<?= $post['link_text_content']; ?>" target="_blank" title="Перейти по ссылке">
            <div class="post-link__info-wrapper">
                <div class="post-link__icon-wrapper">
                    <img src="https://www.google.com/s2/favicons?domain=<?= parse_url('https://' . $post['link_text_content'], PHP_URL_HOST); ?>" alt="Иконка">
                </div>
                <div class="post-link__info">
                    <h3><?= $post['post_header']; ?></h3>
                </div>
            </div>
        </a>
    </div>
</div>
