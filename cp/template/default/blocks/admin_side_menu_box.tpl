<?php if(isset($content_raw)) { ?>
    <?php echo $content_raw; ?>
<?php } else { ?>
    <div class="panel panel-default"<?php if(isset($id)) { ?> id="<?php echo $id; ?>"<?php } ?>>
        <div class="panel-heading"><?php echo $title; ?></div>
        <?php if(isset($content)) { ?>
            <div class="panel-body">
                <?php echo $content; ?>
            </div>
        <?php } else { ?>
            <ul class="list-group">
                <?php echo $list; ?>
            </ul>
        <?php } ?>
    </div>
<?php } ?>