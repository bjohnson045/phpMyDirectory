<?php foreach($categories AS $key=>$category) { ?>
    <?php echo $category; ?><?php if(isset($categories[$key+1])) { ?>, <?php } ?>
<?php } ?>