<form method="post" id="payment-form" action="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_payment_return.php">
    <section>
        <div class="bt-drop-in-wrapper">
            <div id="bt-dropin"></div>
        </div>
    </section>
    <input id="nonce" name="payment_method_nonce" type="hidden" />
    <input id="amount" name="amount" value="<?php echo $balance; ?>" type="hidden">
    <input id="invoice_id" name="invoice_id" value="<?php echo $invoice_id; ?>" type="hidden">
    <input id="pricing_id" name="pricing_id" value="<?php echo $pricing_id; ?>" type="hidden">
    <input id="first_name" name="first_name" value="<?php echo $user_first_name; ?>" type="hidden">
    <input id="last_name" name="last_name" value="<?php echo $user_last_name; ?>" type="hidden">
    <input id="order_id" name="order_id" value="<?php echo $order_id; ?>" type="hidden">
    <button class="button" type="submit"><span>Continue</span></button><i></i>
</form>
<script src="https://js.braintreegateway.com/web/dropin/1.9.4/js/dropin.min.js"></script>
<script>
var form = document.querySelector('#payment-form');
var client_token = "<?php echo($gateway->ClientToken()->generate()); ?>";
braintree.dropin.create({
    authorization: client_token,
    selector: '#bt-dropin',
    paypal: {
        flow: 'vault'                                                                             
    }                                        
}, function (createErr, instance) {
    if (createErr) {
        console.log('Create Error', createErr);
        return;
    }
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        instance.requestPaymentMethod(function (err, payload) {
            if(err) {
                console.log('Request Payment Method Error', err);
                return;
            }
            // Add the nonce to the form and submit
            document.querySelector('#nonce').value = payload.nonce;
            form.submit();
        });
    });
});
</script>