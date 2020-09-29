<div id="<?php echo $name; ?>_container" class="controlset hours_container">
    <ul id="<?php echo $name; ?>_display" class="sortable">
        <?php echo $hours_selected; ?>
    </ul>
    <select id="<?php echo $name; ?>_weekday" name="<?php echo $name; ?>_weekday" class="select">
        <?php echo $days_options; ?>
    </select>
    <select id="<?php echo $name; ?>_start" name="<?php echo $name; ?>_start" style="margin-left: 4px;" class="select">
        <?php echo $times_options; ?>
    </select>
    <select id="<?php echo $name; ?>_end" name="<?php echo $name; ?>_end" style="margin-left: 4px;" class="select">
        <?php echo $times_options; ?>
    </select>
    <button class="btn" id="<?php echo $name; ?>_add" type="button" value="submit" style="margin-left: 4px;"><span><?php echo $lang['add']; ?></span></button>
</div>
<?php echo $javascript; ?>