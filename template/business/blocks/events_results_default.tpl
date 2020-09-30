<div class="panel panel-default">
    <div class="panel-body">
        <h4><a href="<?php echo $this->escape($event['url']); ?>"><?php echo $this->escape($event['title']); ?></a></h4>
        <span class="pull-right"><?php echo $this->escape($event['date_start']); ?></span>
        <?php echo $this->escape($event['description_short']); ?>
    </div>
</div>