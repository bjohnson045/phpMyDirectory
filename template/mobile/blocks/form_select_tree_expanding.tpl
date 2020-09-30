<input type="hidden"<?php echo $attributes; ?> value="<?php echo $value; ?>">
<div id="<?php echo $name; ?>_tree_div" class="tree_select_expanding_wrapper"></div>
<?php if(isset($limit)) { ?>
    <p class="help-block"><?php echo $lang['limit']; ?>: <span id="<?php echo $name; ?>_tree_check_limit">0</span> / <?php echo $limit; ?></p>
<?php } ?>
<?php if(isset($checkall)) { ?>
    <p id="<?php echo $name; ?>_tree_check_links" class="help-block"><a href="#" class="btn btn-mini" id="<?php echo $name; ?>_tree_checkall"><?php echo $lang['check_all']; ?></a> <a href="#" class="btn btn-mini" id="<?php echo $name; ?>_tree_uncheckall"><?php echo $lang['uncheck_all']; ?></a></p>
<?php } ?>
<?php echo $javascript; ?>