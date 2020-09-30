<li class="ui-state-default">
    <a style="float:right;" href="#"><?php echo $lang['remove']; ?></a>
    <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
    <span style="display:inline-block;width:28px;"><?php echo $this->escape($day); ?></span>
    <span style="margin-left: 1px;"><?php echo $this->escape($time1); ?></span>
    <span style="margin-left: 4px;">-</span>
    <span style="margin-left: 4px;"><?php echo $this->escape($time2); ?></span>
    <input type="hidden" name="<?php echo $this->escape($name); ?>[]" value="<?php echo $this->escape($hour); ?>">
</li>