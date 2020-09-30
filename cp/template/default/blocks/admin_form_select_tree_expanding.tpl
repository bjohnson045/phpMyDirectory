<input type="hidden"<?php echo $attributes; ?> value="<?php echo $value; ?>">
<div id="<?php echo $name; ?>_tree_div" class="form-control" style="height: 100%"></div>
<?php if(isset($limit)) { ?>
    <p class="help-block"><?php echo $lang['limit']; ?>: <span id="<?php echo $name; ?>_tree_check_limit">0</span> / <?php echo $limit; ?></p>
<?php } ?>
<?php if(isset($checkall)) { ?>
    <p id="<?php echo $name; ?>_tree_check_links" class="help-block"><a href="#" class="btn btn-default btn-xs" id="<?php echo $name; ?>_tree_checkall"><?php echo $lang['check_all']; ?></a> <a href="#" class="btn btn-default btn-xs" id="<?php echo $name; ?>_tree_uncheckall"><?php echo $lang['uncheck_all']; ?></a></p>
<?php } ?>
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