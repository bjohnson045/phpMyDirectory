<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['block_classifieds_new']; ?></h3>
    </div>
    <div class="panel-body">
    <?php if(count($records)) { ?>
        <?php foreach($records as $record) { ?>
            <?php if($record['thumb'] != '') { ?>
                <p><a href="<?php echo $record['link']; ?>"><img src="<?php echo $record['thumb']; ?>" alt="<?php echo $this->escape($record['title']); ?>" /></a></p>
            <?php } ?>
            <p><a href="<?php echo $record['link']; ?>"><?php echo $this->escape($record['title']); ?></a></p>
            <?php echo $this->escape($record['description']); ?>
        <?php } ?>
    <?php } else { ?>
         <?php echo $lang['block_classifieds_none']; ?>
    <?php } ?>
    </div>
</div>