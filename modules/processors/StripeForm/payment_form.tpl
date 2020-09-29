<script type="text/javascript">
$(document).ready(function(){
    $('#<?php echo $form->id; ?>').submit(function(event) {
        var $form = $(this);

        // Disable the submit button to prevent repeated clicks
        // $form.find('button').prop('disabled', true);
        //console.log('running create');
        Stripe.card.createToken($form, stripeResponseHandler);

        // Prevent the form from submitting with the default action
        return false;
    });
});

var stripeResponseHandler = function(status, response) {
    var $form = $('#<?php echo $form->id; ?>');
    if(response.error) {
        //console.log('got error');
        // Show the errors on the form
        $('#error_container').text(response.error.message);
        $("#error_container").html('');
        addMessage('error',response.error.message,'error_container');
        //$form.find('button').prop('disabled', false);
    } else {
        //console.log('setting token');
        // token contains id, last4, and card type
        var token = response.id;
        // Insert the token into the form so it gets submitted to the server
        $form.append($('<input type="hidden" name="stripeToken" />').val(token));
        // and submit
        $form.get(0).submit();
    }
};
</script>
<div id="error_container"></div>
<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $form->getFieldSetLabel('credit_card'); ?></legend>
    <?php echo $form->getFieldGroup('cc_type',array('name'=>null)); ?>
    <?php echo $form->getFieldGroup('cc_number',array('name'=>null,'data-stripe'=>'number')); ?>
    <?php echo $form->getFieldGroup('cc_expire_month',array('name'=>null,'data-stripe'=>'exp-month')); ?>
    <?php echo $form->getFieldGroup('cc_expire_year',array('name'=>null,'data-stripe'=>'exp-year')); ?>
    <?php echo $form->getFieldGroup('cc_cvv2',array('name'=>null,'data-stripe'=>'cvc')); ?>
</fieldset>
<?php echo $form->getFormActions(); ?>
<?php echo $form->getFieldSetHTML('hidden'); ?>
<?php echo $form->getFormCloseHTML(); ?>
