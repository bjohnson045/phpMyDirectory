<?php foreach($urls AS $url) { ?>
    <a href="<?php echo $this->escape($url['url']); ?>"><?php echo $this->escape($url['url_title']); ?></a><br>
<?php } ?>