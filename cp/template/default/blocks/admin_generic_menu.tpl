<?php foreach($links AS $link) { ?>
    <a class="list-group-item" href="<?php echo $link['url']; ?>"><?php echo $link['text']; ?></a>
<?php } ?>