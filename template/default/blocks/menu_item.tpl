<?php if($level == 0 AND !empty($sub_menu)) { ?>
    <a href="#" data-toggle="collapse" data-target="#sub_menu<?php echo $id; ?>" class="list-group-item<?php if($active) { ?> active<?php } ?>"><?php echo $this->escape_html($link_title); ?><span class="pull-right fa fa-chevron-down"></span></a>
    <?php echo $sub_menu; ?>
<?php } else { ?>
    <a class="<?php if($level > 0) { ?>sub_menu <?php } ?>list-group-item<?php if($active) { ?> active<?php } ?>"<?php echo $target; ?> href="<?php echo $link; ?>"<?php echo $nofollow; ?>><?php echo $this->escape_html($link_title); ?></a>
<?php } ?>