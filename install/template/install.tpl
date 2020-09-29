<?php if($install_instructions == '') { ?>
<?php $terms = nl2br(file_get_contents('../docs/license.txt')); ?>
<script type="text/javascript">
    $(document).ready(function(){
        $('#database_help').popover({'html': true, container: 'body', trigger: 'hover', placement: 'right', title: 'Database Help', content: 'You must create a database before running this installation process. The details for the database you create are entered in this step. The host name is often &quot;localhost&quot; if not otherwise stated by your web host.&nbsp; The table prefix setting adds a prefix to all database tables. (ex: pmd_users)'});
        $('#admin_help').popover({'html': true, container: 'body', trigger: 'hover', placement: 'right', title: 'Administrator Login Help', content: 'The email and password entered here will allow access to the directory administrative area.'});
        $('#license_help').popover({'html': true, container: 'body', trigger: 'hover', placement: 'right', title: 'License Help', content: 'Your license number can be found in your user area.&nbsp; It is in the format:<br> PMDGL-xxxxxxxxxxxx'});
    });
</script>
<div id="termsBox" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="termsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="termsLabel">phpMyDiredctory End User License Agreement</h3>
            </div>
            <div class="modal-body">
                <p style="font-size: 11px">
                    <?php echo $terms; ?>
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div>
<?php if(count($results)) { ?>
    <div class="alert alert-danger text-center">
        <h4>Server Requirements Error</h4>
        <h5><?php echo $results[0]['result']; ?></h5>
        <h6>Please fix this error and try again.</h6>
        <a class="btn btn-default" href="install.php"><i class="glyphicon glyphicon-refresh"></i> Try Again</a>
    </div>
<?php } else { ?>
    <div class="alert alert-success" style="text-align: center">
        <h4>Server Requirements Successful</h4>
        Your server meets the requirements to run phpMyDirectory.
    </div>
    <form name="submit_form" id="submit_form" method="post" class="form-horizontal">
        <div class="row">
        <div class="col-xs-12">
        <fieldset>
            <legend>Administrator Login Details <i id="admin_help" class="glyphicon glyphicon-question-sign text-muted"></i></legend>
            <?php if($errors['admin_details'] != '') { ?>
                <div class="alert alert-danger">
                    <?php echo $errors['admin_details']; ?>
                </div>
            <?php } elseif($errors['password_mismatch'] != '') { ?>
                <div class="alert alert-danger">
                    <?php echo $errors['password_mismatch']; ?>
                </div>
            <?php } ?>
            <div class="form-group">
                <label class="col-lg-10 control-label">Email:</label>
                <div class="col-lg-14">
                    <input class="form-control" type="text" id="admin_email" name="admin_email" size="31" value="<?php echo htmlspecialchars($_POST['admin_email']); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-10 control-label">Password:</label>
                <div class="col-lg-14">
                    <input class="form-control" type="text" name="admin_pass" size="20" value="<?php echo htmlspecialchars($_POST['admin_pass']); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-10 control-label">Password Repeat:</label>
                <div class="col-lg-14">
                    <input class="form-control" type="text" name="admin_pass2" size="20" value="<?php echo htmlspecialchars($_POST['admin_pass2']); ?>">
                </div>
            </div>
        </fieldset>
        <fieldset>
            <?php $terms = nl2br(file_get_contents('../docs/license.txt')); ?>
            <legend>License / Terms and Conditions <i id="license_help" class="glyphicon glyphicon-question-sign text-muted"></i></legend>
            <?php if($errors['license_format'] != '' OR $errors['terms'] != '') { ?>
                <div class="alert alert-danger">
                    <?php if($errors['terms'] != '') { ?>
                        <?php if($errors['license_format'] != '') { ?>
                            <p><?php echo $errors['terms']; ?></p>
                        <?php } else { ?>
                            <?php echo $errors['terms']; ?>
                        <?php } ?>
                    <?php } ?>
                    <?php if($errors['license_format'] != '') { ?>
                        <?php echo $errors['license_format']; ?>
                    <?php } ?>
                </div>
            <?php } ?>
            <div class="form-group">
                <label class="col-lg-10 control-label">License Number:</label>
                <div class="col-lg-14">
                    <input class="form-control" type="text" id="license" name="license" value="<?php echo htmlspecialchars($_POST['license']); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-10 control-label">&nbsp;</label>
                <div class="col-lg-14">
                    <input type="checkbox" name="terms_agree" value="YES" <?php if(isset($_POST['terms_agree'])) echo 'CHECKED'; ?>> I agree to the  <a href="#termsBox" data-toggle="modal">phpMyDirectory terms and conditions</a>
                </div>
            </div>
        </fieldset>
        </div>
        <div class="col-xs-11 col-xs-offset-1">
        <fieldset>
            <legend>Database Connection Details <i id="database_help" class="glyphicon glyphicon-question-sign text-muted"></i></legend>
            <?php if($errors['database_connect'] != '') { ?>
                <div class="alert alert-danger">
                    <?php echo $errors['database_connect']; ?>
                </div>
            <?php } elseif($errors['prefix_exists'] != '') { ?>
                <div class="alert alert-danger">
                    <?php echo $errors['prefix_exists']; ?>
                    <label><input type="checkbox" name="prefix_overwrite" value="YES" <?php if(isset($_POST['prefix_overwrite'])) echo 'CHECKED'; ?>> Overwrite tables</label>
                </div>
            <?php } elseif($errors['prefix_format'] != '') { ?>
                <div class="alert alert-danger">
                    <?php echo $errors['prefix_format']; ?>
                </div>
            <?php } ?>
            <div class="form-group">
                <label class="col-lg-10 control-label">Database Host Name:</label>
                <div class="col-lg-14">
                    <input class="form-control" type="text" name="hostname" value="<?php echo htmlspecialchars(($_POST['hostname'] == '') ? 'localhost' : $_POST['hostname']); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-10 control-label">Database Name:</label>
                <div class="col-lg-14">
                    <input class="form-control" type="text" name="db_name" value="<?php echo htmlspecialchars($_POST['db_name']); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-10 control-label">Database Username:</label>
                <div class="col-lg-14">
                    <input class="form-control" type="text" name="db_user" value="<?php echo htmlspecialchars($_POST['db_user']); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-10 control-label">Database Password:</label>
                <div class="col-lg-14">
                    <input class="form-control" type="text" name="db_pass" value="<?php echo htmlspecialchars($_POST['db_pass']); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-10 control-label">Table Prefix:</label>
                <div class="col-lg-14">
                    <input class="form-control" type="text" name="prefix" value="<?php echo htmlspecialchars(($_POST['prefix'] == '') ? 'pmd_' : $_POST['prefix']); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-10 control-label">Character Set:</label>
                <div class="col-lg-14">
                    <select class="form-control" name="charset"><option value="utf8"<?php if($_POST['charset'] == 'utf8') { ?> selected="selected"<?php } ?>>utf8 (recommended)</option><option value=""<?php if(isset($_POST['charset']) AND $_POST['charset'] == '') { ?> selected="selected"<?php } ?>>Server Default</option></select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-10 control-label">Database Port:</label>
                <div class="col-lg-14">
                    <input class="form-control" type="text" name="db_port" value="<?php echo htmlspecialchars(($_POST['db_port'] == '') ? '3306' : $_POST['db_port']); ?>">
                </div>
            </div>
        </fieldset>
        </div>
        </div>
        <div class="row row-complete text-center">
            <div class="col-xs-24">
                <p><input type="submit" class="btn btn-lg btn-success" value="Complete Installation" id="complete" name="complete"></p>
            </div>
        </div>
    </form>
    <?php } ?>
<?php } else {
    echo '<br />'.$install_instructions;
}?>