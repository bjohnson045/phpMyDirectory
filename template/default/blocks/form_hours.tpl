<div id="<?php echo $name; ?>_container" class="hours_container">
    <div class="row">
        <div class="col-lg-5">
            <ul id="<?php echo $name; ?>_display" class="sortable list-unstyled">
                <?php echo $hours_selected; ?>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-3">
            <select id="<?php echo $name; ?>_weekday" name="<?php echo $name; ?>_weekday" class="form-control">
                <?php echo $days_options; ?>
            </select>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-3">
            <select id="<?php echo $name; ?>_start" name="<?php echo $name; ?>_start" class="form-control">
                <?php echo $times_options; ?>
            </select>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-3">
            <select id="<?php echo $name; ?>_end" name="<?php echo $name; ?>_end" class="form-control">
                <?php echo $times_options; ?>
            </select>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2">
            <button class="btn btn-default" id="<?php echo $name; ?>_add" type="button" value="submit" style="margin-left: 4px;"><span><?php echo $lang['add']; ?></span></button>
            <?php if($hours_24) { ?>
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="<?php echo $name; ?>_24_hours" name="<?php echo $name; ?>_24_hours" value="24"<?php if($value == '24') { ?>checked="checked"<?php } ?>> <?php echo $hours_24_label; ?>
                </label>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<?php echo $javascript; ?>