<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $this->escape($title); ?></h3>
    </div>
    <?php if(count($featured_classifieds)) { ?>
        <ul class="list-group">
        <?php foreach($featured_classifieds as $classified) { ?>
            <li class="list-group-item">
                <?php if($classified['thumb'] != '') { ?>
                    <p><a href="<?php echo $classified['link']; ?>"><img src="<?php echo $classified['thumb']; ?>" alt="<?php echo $this->escape($classified['title']); ?>" /></a></p>
                <?php } ?>
                <h5 class="list-group-item-heading"><a href="<?php echo $classified['link']; ?>"><?php echo $this->escape($classified['title']); ?></a></h5>
                <p class="list-group-item-text"><?php echo $this->escape($classified['description']); ?></p>
            </li>
        <?php } ?>
        </ul>
    <?php } else { ?>
        <div class="panel-body">
            <?php echo $lang['block_classifieds_none']; ?>
        </div>
    <?php } ?>
</div>