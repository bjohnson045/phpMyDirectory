<?php foreach($items as $item) { ?>
    <a href="<?php echo $this->escape($item['permalink']); ?>" target="_blank"><?php echo $this->escape($item['title']); ?></a> - <?php echo $this->escape($item['data']); ?><br />
    <?php echo $this->escape_html($item['content']); ?><br /><br />
<?php } ?>
