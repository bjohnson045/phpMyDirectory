<?php if(is_array($events) AND count($events)) { ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['block_events_upcoming']; ?><a class="pull-right" href="<?php echo BASE_URL; ?>/xml.php?type=rss_events_new"><i class="fa fa-rss"></i></a></h3>
    </div>
    <ul class="list-group">
        <?php foreach($events as $event) { ?>
            <li class="list-group-item">
                <h5 class="list-group-item-heading"><a href="<?php echo $event['url']; ?>" title="<?php echo $this->escape($event['title']); ?>"><?php echo $this->escape($event['title']); ?></a></h5>
                <?php if(!empty($event['description_short'])) { ?>
                    <p><?php echo $this->escape_html($event['description_short']); ?></p>
                <?php } ?>
                <p><small class="text-muted tiny"><?php echo $this->escape($event['date_start']); ?></small></p>
            </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>