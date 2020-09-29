<script type="text/javascript">
$(document).ready(function(){
    $('a[id^="email_log_message_link"]').click(function() {
        var dialog = $('<div style="display:none"></div>').appendTo('body');
        $.ajax({
            data:({
                action:'admin_email_log_view',
                id:$(this).attr('id')
            }),
            success:function(data) {
                dialog.html(data);
                dialog.dialog({
                    title: '<?php echo $lang['admin_email_log_view_message']; ?>',
                    width: 650,
                    height: 450,
                    modal: true,
                    resizable: false,
                    buttons: {
                        "Close": function() {
                            $(this).dialog("close");
                            dialog.remove();
                        }
                    },
                });
            }
        });
        return false;
    });
    $('#api_generate').click(function() {
        $.ajax({
            data:({
                action:'admin_generate_api_key',
                id: <?php echo $id; ?>
            }),
            success:function(data) {
                $('#api_password').html(data);
            }
        });
        return false;
    });
});
</script>
<?php echo $users_summary_header; ?>
<div class="row">
    <div class="col-md-10">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $lang['admin_users_information']; ?></div>
            <div class="panel-body">
                <table class="table table-condensed table-borderless">
                    <?php if($profile_image) { ?>
                        <tr><td colspan="2"><img src="<?php echo $profile_image; ?>"/></td></tr>
                    <?php } ?>
                    <tr><td><?php echo $lang['admin_users_id']; ?>:</td><td><?php echo $id; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_username']; ?>:</td><td><?php echo $login; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_first_name']; ?>:</td><td><?php echo $user_first_name; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_last_name']; ?>:</td><td><?php echo $user_last_name; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_organization']; ?>:</td><td><?php echo $user_organization; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_email']; ?>:</td><td><?php echo $user_email; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_address1']; ?>:</td><td><?php echo $user_address1; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_address2']; ?>:</td><td><?php echo $user_address2; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_city']; ?>:</td><td><?php echo $user_city; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_state']; ?>:</td><td><?php echo $user_state; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_zipcode']; ?>:</td><td><?php echo $user_zip; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_country']; ?>:</td><td><?php echo $user_country; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_phone']; ?>:</td><td><?php echo $user_phone; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_fax']; ?>:</td><td><?php echo $user_fax; ?></td></tr>
                </table>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $lang['admin_users_other_information']; ?></div>
            <div class="panel-body">
                <table class="table table-condensed table-borderless">
                    <tr><td><?php echo $lang['admin_users_tax_exempt']; ?>:</td><td><?php echo $tax_exempt; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_disable_overdue_notices']; ?>:</td><td><?php echo $disable_overdue_notices; ?></td></tr>
                    <tr>
                        <td style="vertical-align: top;"><?php echo $lang['admin_users_groups']; ?>:</td>
                        <td>
                            <?php if($user_groups) { ?>
                                <?php foreach($user_groups AS $group) { ?>
                                    <?php echo $group; ?><br />
                                <?php } ?>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><?php echo $lang['admin_users_email_lists']; ?>:</td>
                        <td>
                            <?php if($email_lists) { ?>
                                <?php foreach($email_lists AS $email_list) { ?>
                                    <?php echo $email_list['title']; ?><br />
                                <?php } ?>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                    <tr><td><?php echo $lang['admin_users_local_time']; ?>:</td><td><?php echo $local_time; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_created']; ?>:</td><td><?php echo $created; ?></td></tr>
                    <tr><td><?php echo $lang['admin_users_logged_in']; ?>:</td><td><?php echo $logged_in; ?></td></tr>
                    <tr>
                        <td><?php echo $lang['admin_users_last_logged_in']; ?>:</td>
                        <td>
                            <?php if($logged_last) { ?>
                                <?php echo $logged_last; ?>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $lang['admin_users_last_ip_address']; ?>:</td>
                        <td>
                            <?php if($logged_ip) { ?>
                                <?php echo $logged_ip; ?>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $lang['admin_users_last_hostname']; ?>:</td>
                        <td>
                            <?php if($logged_host) { ?>
                                <?php echo $logged_host; ?>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                    <?php if(!empty($login_providers)) { ?>
                        <tr><td><?php echo $lang['admin_users_login_module']; ?>:</td><td><?php echo $login_providers; ?></td></tr>
                    <?php } ?>
                    <?php if($fields) { ?>
                        <?php foreach($fields AS $field) { ?>
                            <tr>
                                <td><?php echo $field['name']; ?>:</td>
                                <td><?php echo ${'custom_'.$field['id']}; ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $lang['admin_users_actions']; ?></div>
                <div class="panel-body">
                <p>
                <a class="btn btn-default" target="_blank" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>index.php?user_login_field=login&user_login=<?php echo $this->escape($login); ?>"><?php echo $lang['admin_users_login_as_user']; ?></a>
                <a class="btn btn-default" href="admin_users_summary.php?action=reset_password&id=<?php echo $id; ?>"><?php echo $lang['admin_users_reset_and_send_password']; ?></a><br />
                </p>
                <p>
                <a class="btn btn-default" href="<?php echo BASE_URL_ADMIN; ?>/admin_invoices.php?action=add&user_id=<?php echo $id; ?>"><?php echo $lang['admin_users_add_invoice']; ?></a>
                <a class="btn btn-default" href="<?php echo BASE_URL_ADMIN; ?>/admin_orders_add.php?user_id=<?php echo $id; ?>"><?php echo $lang['admin_users_add_order']; ?></a>
                <a class="btn btn-default" href="<?php echo BASE_URL_ADMIN; ?>/admin_users_merge.php?from_id=<?php echo $id; ?>"><?php echo $lang['admin_users_merge']; ?></a><br />
                </p>
                <p>
                <a class="btn btn-xs btn-danger" onclick="return confirm('<?php echo $lang['messages_confirm']; ?>');" href="<?php echo BASE_URL_ADMIN; ?>/admin_users.php?action=delete&id=<?php echo $id; ?>"><?php echo $lang['admin_delete']; ?></a>
                </p>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $lang['admin_users_recent_emails']; ?></div>
            <div class="panel-body">
                <?php if($recent_emails) { ?>
                    <p>
                    <?php foreach($recent_emails AS $recent_email) { ?>
                        <?php echo $recent_email['date']; ?> - <a id="email_log_message_link_<?php echo $recent_email['id']; ?>" href="#"><?php echo $recent_email['subject']; ?></a><br />
                    <?php } ?>
                    </p>
                    <p><a class="btn btn-default btn-xs" href="admin_email_log.php?user_id=<?php echo $id; ?>"><?php echo $lang['admin_users_view_all']; ?> &raquo;</a></p>
                <?php } else { ?>
                    <?php echo $lang['admin_users_none']; ?>
                <?php } ?>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $lang['admin_users_send_email']; ?></div>
            <div class="panel-body">
                <?php echo $email_form->getFormOpenHTML(); ?>
                <?php echo $email_form->getFieldHTML('email'); ?><br />
                <?php echo $email_form->getFieldHTML('submit'); ?>
                <?php echo $email_form->getFormCloseHTML(); ?>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $lang['admin_users_comments']; ?></div>
            <div class="panel-body">
                <?php if(empty($comments)) { ?>
                    No comments
                <?php } else { ?>
                    <?php echo $comments; ?>
                <?php } ?>
            </div>
        </div>
        <?php if($api_username) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">API Key</div>
            <div class="panel-body">
                <p><strong>API Username:</strong><br><?php echo $api_username; ?></p>
                <p><strong>API Password:</strong><br>
                    <span id="api_password"></span>
                    <a class="btn btn-default" href="#" id="api_generate"><span class="glyphicon glyphicon-lock"></span> Generate API Password</a>
                </p>
            </div>
        </div>
        <?php } ?>
        <?php if($map) { ?>
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $lang['admin_users_location']; ?></div>
            <div class="panel-body">
                <?php echo $map; ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<?php echo $content; ?>