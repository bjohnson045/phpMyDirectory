<script type="text/javascript">
function successCallback(data) {
    console.log('success');
    console.log(data.response.token.token);
    $('#token').val(data.response.token.token);
    //$('#<?php echo $form->id; ?>').attr("submitted","false");
    //$('#<?php echo $form->id; ?>').submit();
    console.log('submitting');
    document.getElementById('<?php echo $form->id; ?>').submit();
    console.log('submitted');
}

function tokenSet() {
    if($('#token').val() != '') {
        alert('returning true');
        return true;
    } else {
        console.log('returning false');
        return false;
    }
}

function errorCallback(data) {
    console.log('returning error');
    $("#error_container").html('');
    addMessage('error',data.errorMsg,'error_container');
}

function retrieveToken() {
    console.log('getting token');
    TCO.requestToken(successCallback, errorCallback, '<?php echo $form->id; ?>');
}
</script>
<div id="error_container"></div>
<?php echo $form->getFormOpenHTML(array('onsubmit'=>'return tokenSet()')); ?>
<fieldset>
    <legend><?php echo $form->getFieldSetLabel('credit_card'); ?></legend>
    <?php echo $form->getFieldGroup('cc_type',array('name'=>null)); ?>
    <?php echo $form->getFieldGroup('cc_number',array('name'=>null,'id'=>'ccNo')); ?>
    <?php echo $form->getFieldGroup('cc_expire_month',array('name'=>null,'id'=>'expMonth')); ?>
    <?php echo $form->getFieldGroup('cc_expire_year',array('name'=>null,'id'=>'expYear')); ?>
    <?php echo $form->getFieldGroup('cc_cvv2',array('name'=>null,'id'=>'cvv')); ?>
</fieldset>
<?php echo $form->getFormActions(); ?>
<?php echo $form->getFieldSetHTML('hidden'); ?>
<?php echo $form->getFormCloseHTML(); ?>