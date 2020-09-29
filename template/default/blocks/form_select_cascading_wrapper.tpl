<div id="<?php echo $name; ?>_container">
    <?php echo $fields; ?>
    <?php if(is_array($value)) { ?>
        <?php foreach($value AS $key=>$value_item) { ?>
            <input type="hidden" id="<?php echo $name; ?>" name="<?php echo $name; ?>[]" value="<?php echo $value_item; ?>" />
        <?php } ?>
    <?php } else { ?>
        <input type="hidden" id="<?php echo $name; ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />
    <?php } ?>
</div>
<?php echo $javascript; ?>
<?php if($search) { ?>
    <div class="input-group" style="margin-top: 5px;">
        <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
        <input class="form-control" id="<?php echo $name; ?>_search" name="<?php echo $name; ?>_search" placeholder="<?php echo $label; ?>">
    </div>
    <script type="text/javascript">
    $(document).ready(function(){
        $("#<?php echo $name; ?>_search").qtip("option","style.classes","qtip-bootstrap qtip-shadow");
    });
    </script>
<?php } ?>