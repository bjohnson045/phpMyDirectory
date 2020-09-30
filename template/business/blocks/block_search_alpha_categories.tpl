<?php if(count($alpha_letters) > 0) { ?>
    <div class="text-center hidden-xs">
        <p>
            <?php echo $this->escape($title); ?>:
            <?php foreach($alpha_letters as $value) { ?>
                    <a href="<?php echo BASE_URL; ?>/sitemap.php?letter=<?php echo urlencode($value); ?>&amp;id=<?php echo $id; ?>&amp;type=categories"><u><?php echo $this->escape($value); ?></u></a>&nbsp;
            <?php } ?>
        </p>
    </div>
<?php } ?>