<ul class="octavius-rocks-variants">
<?php
global $post;
foreach ($variants as $slug => $name) {
    $values = $this->variants->get_post_metas($post->ID, $slug);
    wp_get_attachment_image($values->attachment_id);
    ?>
    
    <li class="octavius-rocks-variant">
        <label for="octavius-rocks-<?php echo $slug; ?>"><?php echo $name; ?></label>
        <div class="octavius-rocks-contents">
            
            <p><input id="octavius-rocks-<?php echo $slug; ?>" type="text" 
            name="octavius_ab[<?php echo $slug; ?>][title]" placeholder="Title" value="<?php echo $values->title; ?>" /></p>

            <input type="hidden" class="octavius-ab-image-id" name="octavius_ab[<?php echo $slug; ?>][attachment_id]" 
            value="<?php echo $values->attachment_id; ?>" />

            <?php echo wp_get_attachment_image($values->attachment_id); ?>
            
            <p><input type="button" class="octavius-ab-image button" value="Image" size="25" /></p>

            <p><textarea name="octavius_ab[<?php echo $slug; ?>][excerpt]" placeholder="Excerpt"><?php echo $values->excerpt; ?></textarea></p>
        </div>
    </li>
    <?php
}
?></ul>