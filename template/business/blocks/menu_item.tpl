<li class="<?php if(!empty($sub_menu)) { ?>dropdown <?php } ?><?php if($active) { ?>active<?php } ?>">
    <?php if(!empty($sub_menu)) { ?>
        <a class="dropdown-toggle" data-toggle="dropdown" <?php echo $target; ?> href="<?php echo $link; ?>"<?php echo $nofollow; ?>><?php echo $this->escape_html($link_title); ?> <b class="caret"></b></a>
    <?php } else { ?>
        <a <?php echo $target; ?> href="<?php echo $link; ?>"<?php echo $nofollow; ?>><?php echo $this->escape_html($link_title); ?></a>
    <?php } ?>
    <?php echo $sub_menu; ?>
</li>