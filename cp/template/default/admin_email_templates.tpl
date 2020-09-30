<h1><?php echo $title ?></h1>
<?php if(!isset($_GET['action'])) { ?>
<div class="btn-group" style="margin-bottom: 10px;">
    <a href="admin_email_templates.php" class="btn btn-default btn-sm<?php if(!isset($_GET['filter'])) { ?> active<?php } ?>">All</a>
    <a href="admin_email_templates.php?filter=administrator" class="btn btn-default btn-sm<?php if($_GET['filter'] == 'administrator') { ?> active<?php } ?>"><?php echo $lang['admin_email_templates_type_administrator']; ?></a>
    <a href="admin_email_templates.php?filter=notadministrator" class="btn btn-default btn-sm<?php if($_GET['filter'] == 'notadministrator') { ?> active<?php } ?>"><?php echo $lang['admin_email_templates_type_not_administrator']; ?></a>
    <a href="admin_email_templates.php?filter=general" class="btn btn-default btn-sm<?php if($_GET['filter'] == 'general') { ?> active<?php } ?>"><?php echo $lang['admin_email_templates_type_general']; ?></a>
    <a href="admin_email_templates.php?filter=invoice" class="btn btn-default btn-sm<?php if($_GET['filter'] == 'invoice') { ?> active<?php } ?>"><?php echo $lang['admin_email_templates_type_invoice']; ?></a>
    <a href="admin_email_templates.php?filter=listing" class="btn btn-default btn-sm<?php if($_GET['filter'] == 'listing') { ?> active<?php } ?>"><?php echo $lang['admin_email_templates_type_listing']; ?></a>
    <a href="admin_email_templates.php?filter=classified" class="btn btn-default btn-sm<?php if($_GET['filter'] == 'classified') { ?> active<?php } ?>"><?php echo $lang['admin_email_templates_type_classified']; ?></a>
    <a href="admin_email_templates.php?filter=event" class="btn btn-default btn-sm<?php if($_GET['filter'] == 'event') { ?> active<?php } ?>"><?php echo $lang['admin_email_templates_type_event']; ?></a>
    <a href="admin_email_templates.php?filter=job" class="btn btn-default btn-sm<?php if($_GET['filter'] == 'job') { ?> active<?php } ?>"><?php echo $lang['admin_email_templates_type_job']; ?></a>
    <a href="admin_email_templates.php?filter=order" class="btn btn-default btn-sm<?php if($_GET['filter'] == 'order') { ?> active<?php } ?>"><?php echo $lang['admin_email_templates_type_order']; ?></a>
    <a href="admin_email_templates.php?filter=review" class="btn btn-default btn-sm<?php if($_GET['filter'] == 'review') { ?> active<?php } ?>"><?php echo $lang['admin_email_templates_type_review']; ?></a>
    <a href="admin_email_templates.php?filter=blog" class="btn btn-default btn-sm<?php if($_GET['filter'] == 'blog') { ?> active<?php } ?>"><?php echo $lang['admin_email_templates_type_blog']; ?></a>
    <a href="admin_email_templates.php?filter=user" class="btn btn-default btn-sm<?php if($_GET['filter'] == 'user') { ?> active<?php } ?>"><?php echo $lang['admin_email_templates_type_user']; ?></a>
</div>
<?php } ?>
<?php echo $content; ?>