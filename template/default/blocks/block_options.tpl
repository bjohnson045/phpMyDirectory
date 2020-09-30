<?php if($languages_array OR $templates_array) { ?>
<form action="#" method="post" class="form-inline" role="form">
    <?php if($languages_array) { ?>
        <div class="form-group">
            <label><?php echo $lang['block_options_language']; ?>:</label>
            <select class="form-control" name="lang" onchange="this.form.submit();">
            <?php foreach($languages_array as $id=>$language) { ?>
                <option value="<?php echo $this->escape($id); ?>"<?php if($id == $current_language) { ?> selected="selected"<?php } ?>><?php echo $language; ?></option>
            <?php } ?>
            </select>
        </div>
    <?php } ?>
    <?php if($templates_array) { ?>
        <div class="form-group">
            <label><?php echo $lang['block_options_template']; ?>:</label>
            <select class="form-control" name="template" onchange="this.form.submit();">
            <?php foreach($templates_array as $template) { ?>
                <option value="<?php echo $this->escape($template); ?>"<?php if($template == $current_template) { ?> selected="selected"<?php } ?>><?php echo $template; ?></option>
            <?php } ?>
            </select>
        </div>
    <?php } ?>
</form>
<?php } ?>