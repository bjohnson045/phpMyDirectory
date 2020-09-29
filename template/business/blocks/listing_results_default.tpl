<div class="panel panel-default listing_results_result">
    <div class="panel-body">
        <div class="pull-left hidden-xs">
            <?php if($logo_url) { ?>
                <a class="pull-left img-thumbnail" href="<?php echo $link; ?>"><img src="<?php echo $logo_url; ?>" alt="<?php echo $this->escape($title); ?>" /></a>
            <?php } else { ?>
                <i class="pull-left fa fa-picture-o fa-5x"></i>
            <?php } ?>
        </div>
        <div class="pull-right">
            <?php echo $rating; ?>
        </div>
        <div class="pull-right clear-right">
            <?php if($new) { ?>
                <span class="label label-success"><span class="fa fa-sun-o"></span> <?php echo $lang['public_listing_new']; ?></span>
            <?php } ?>
            <?php if($updated) { ?>
                <span class="label label-info"><span class="fa fa-pencil"></span> <?php echo $lang['public_listing_updated']; ?></span>
            <?php } ?>
            <?php if($hot) { ?>
                <span class="label label-danger"><span class="glyphicon glyphicon-fire"></span> <?php echo $lang['public_listing_hot']; ?></span>
            <?php } ?>
        </div>
        <h4><a href="<?php echo $link; ?>"><?php echo $this->escape($title); ?></a></h4>
        <?php if($map_marker AND $map_marker <= 20) { ?>
            <div class="listing_results_map_marker listing_results_map_marker<?php echo $map_marker; ?>"></div>
        <?php } ?>
        <p class="listing_results_address"><small><?php echo $this->escape($address); ?></small></p>
        <p class="text-muted"><?php echo $this->escape_html($short_description); ?></p>
        <!--
        <?php if($score) { ?>
            <p><?php echo $score; ?>%</p>
        <?php } ?>
        <?php if($zip_distance) { ?>
            <p><?php echo $zip_distance; ?></p>
        <?php } ?>
        -->
    </div>
</div>