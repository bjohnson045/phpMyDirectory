<div id="<?php echo $id; ?>_controlset" class="controlset">
    <?php if($columns AND count($columns) > 0) { ?>
        <table>
            <tr>
                <td style="vertical-align: top; padding-right: 15px;">
    <?php } ?>
    <?php foreach($fields AS $key=>$field) { ?>
        <?php echo $field; ?>
        <?php if(isset($new_column_indexes) AND in_array($key,$new_column_indexes)) { ?>
            </td><td style="vertical-align: top; padding-right: 15px;">
        <?php } ?>
    <?php } ?>
    <?php if($columns AND count($columns) > 0) { ?>
                </td>
            </tr>
        </table>
    <?php } ?>
    <?php if(isset($checkall)) { ?>
        <p class="note">
            <a href="#" onclick="$('#<?php echo $id; ?>_controls :checkbox').each(function(){this.checked = true;}); return false;"><?php echo $lang['check_all']; ?></a>
            <a href="#" onclick="$('#<?php echo $id; ?>_controls :checkbox').each(function(){this.checked = false;}); return false;"><?php echo $lang['uncheck_all']; ?></a>
        </p>
    <?php } ?>
    <?php if(isset($html)) { ?>
        <?php echo $html; ?>
    <?php } ?>
</div>