<?php foreach($items as $item) { ?>
    <li class="list-group-item">
        <a href="<?php echo $this->escape($item['permalink']); ?>" target="_blank"><?php echo $this->escape($item['title']); ?></a><br />
    </li>
<?php } ?>