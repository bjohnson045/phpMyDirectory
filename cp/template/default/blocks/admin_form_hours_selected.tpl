<li class="well well-sm">
    <i class="glyphicon glyphicon-resize-vertical" style="margin-right: 5px"></i><?php echo $this->escape($day); ?> <?php echo $this->escape($time1); ?> - <?php echo $this->escape($time2); ?><a class="close" aria-hidden="true">&times;</a>
    <input type="hidden" name="<?php echo $this->escape($name); ?>[]" value="<?php echo $this->escape($hour); ?>">
</li>