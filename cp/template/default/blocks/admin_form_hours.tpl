<div id="<?php echo $name; ?>_container" class="controlset hours_container">
    <div id="<?php echo $name; ?>_container_options"<?php if($value == '24') { ?> style="display:none;"<?php } ?>>
        <ul id="<?php echo $name; ?>_display" class="sortable">
            <?php echo $hours_selected; ?>
        </ul>
        <select id="<?php echo $name; ?>_weekday" name="<?php echo $name; ?>_weekday" class="form-control">
            <?php echo $days_options; ?>
        </select>
        <select id="<?php echo $name; ?>_start" name="<?php echo $name; ?>_start" style="margin-left: 4px;" class="form-control">
            <?php echo $times_options; ?>
        </select>
        <select id="<?php echo $name; ?>_end" name="<?php echo $name; ?>_end" style="margin-left: 4px;" class="form-control">
            <?php echo $times_options; ?>
        </select>
        <button class="btn btn-default" id="<?php echo $name; ?>_add" type="button" value="submit" style="margin-left: 4px;"><span><?php echo $lang['add']; ?></span></button>
    </div>
    <?php if($hours_24) { ?>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="<?php echo $name; ?>_24_hours" name="<?php echo $name; ?>_24_hours" value="24"<?php if($value == '24') { ?>checked="checked"<?php } ?>> <?php echo $hours_24_label; ?>
        </label>
    </div>
    <?php } ?>
</div>
<?php echo $javascript; ?>