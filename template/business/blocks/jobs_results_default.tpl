<div class="panel panel-default">
    <div class="panel-body">
        <h4><a href="<?php echo $this->escape($job['url']); ?>"><?php echo $this->escape($job['title']); ?></a></h4>
        <span class="pull-right"><?php echo $this->escape($job['date']); ?></span>
        <?php echo $this->escape($job['description_short']); ?>
    </div>
</div>