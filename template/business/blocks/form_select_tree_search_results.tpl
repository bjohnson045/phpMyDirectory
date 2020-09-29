<?php if($results) { ?>
    <?php foreach($results AS $result) { ?>
    <p>
        <?php foreach($result['path'] AS $key=>$path) { ?>
            <a class="<?php echo $this->escape($name); ?>_search_link" href="#" data-id="<?php echo $path['id']; ?>" data-id-path="<?php echo $path['id_path']; ?>"><?php echo $this->escape($path['title']); ?></a>
            <?php if($key < (count($result['path']) - 1)) { ?> > <?php } ?>
        <?php } ?>
    </p>
    <?php } ?>
<?php } ?>