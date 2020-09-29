<?php if(count($alpha_letters) > 0) { ?>
    <div class="text-center hidden-xs">
        <?php echo $lang['public_general_search_search']; ?>:
        <p>
            <?php foreach($alpha_letters as $value) { ?>
                <a href="<?php echo BASE_URL; ?>/search_results.php?alpha=<?php echo urlencode($value); ?>"><u><?php echo $this->escape($value); ?></u></a>&nbsp;
            <?php } ?>
        </p>
    </div>
<?php } ?>