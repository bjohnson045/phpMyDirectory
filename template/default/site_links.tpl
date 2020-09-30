<?php if(sizeof($links) > 0) { ?>
    <?php foreach($links as $key=>$value) { ?>
        <h2><?php echo $this->escape($value['title']); ?></h2>
        <?php if($value['description']) { ?>
            <p><?php echo $this->escape($value['description']); ?></p>
        <?php } ?>
        <p><strong><?php echo $lang['public_site_links_example']; ?>:</strong></p>
        <p><?php echo $value['example']; ?></p>
        <p><strong>Code:</strong></p>
        <textarea class="form-control" style="width: 550px; height: 100px;"><?php echo $value['javascript']; ?></textarea>
    <?php } ?>
<?php } ?>