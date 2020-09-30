<table class="table table-condensed table-borderless col-md-8">
    <tr><td><?php echo $lang['admin_users_id']; ?>:</td><td><?php echo $id; ?></td></tr>
    <tr><td><?php echo $lang['admin_users_username']; ?>:</td><td><?php echo $login; ?></td></tr>
    <tr><td><?php echo $lang['admin_users_first_name']; ?>:</td><td><?php echo $user_first_name; ?></td></tr>
    <tr><td><?php echo $lang['admin_users_last_name']; ?>:</td><td><?php echo $user_last_name; ?></td></tr>
    <tr><td><?php echo $lang['admin_users_organization']; ?>:</td><td><?php echo $user_organization; ?></td></tr>
    <tr><td><?php echo $lang['admin_users_email']; ?>:</td><td><a href="admin_email_send.php?template=new&user_id=<?php echo $id; ?>"><?php echo $user_email; ?></a></td></tr>
    <tr><td><?php echo $lang['admin_users_address1']; ?>:</td><td><?php echo $user_address1; ?></td></tr>
    <tr><td><?php echo $lang['admin_users_address2']; ?>:</td><td><?php echo $user_address2; ?></td></tr>
    <tr><td><?php echo $lang['admin_users_city']; ?>:</td><td><?php echo $user_city; ?></td></tr>
    <tr><td><?php echo $lang['admin_users_state']; ?>:</td><td><?php echo $user_state; ?></td></tr>
    <tr><td><?php echo $lang['admin_users_zipcode']; ?>:</td><td><?php echo $user_zip; ?></td></tr>
    <tr><td><?php echo $lang['admin_users_country']; ?>:</td><td><?php echo $user_country; ?></td></tr>
    <tr><td><?php echo $lang['admin_users_phone']; ?>:</td><td><?php echo $user_phone; ?></td></tr>
    <tr><td><?php echo $lang['admin_users_fax']; ?>:</td><td><?php echo $user_fax; ?></td></tr>
</table>