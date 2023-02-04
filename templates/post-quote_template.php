<div class="post-details__image-wrapper post-quote">
    <div class="post__main">
        <blockquote>
            <p>
                <?= str_replace('&amp;#13;&amp;#10;', "<br>", $post['text_content']); ?>
            </p>
            <cite><?= $post['quote_origin']; ?></cite>
        </blockquote>
    </div>
</div>
