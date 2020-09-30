<?php echo $header; ?>
<div class="row row-offcanvas row-offcanvas-left">
    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-4 sidebar-offcanvas" id="sidebar">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $lang['user_general_menu']; ?></h3>
            </div>
            <div class="list-group">
                <?php if(LOGGED_IN) { ?>
                    <a class="list-group-item" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>index.php"><?php echo $lang['user_general_account_summary']; ?></a>
                    <a class="list-group-item" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_account.php"><?php echo $lang['user_general_edit_account']; ?></a>
                    <a class="list-group-item" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_favorites.php"><?php echo $lang['user_general_favorites']; ?></a>
                    <?php if($this->checkPermission('user_advertiser')) { ?>
                        <a class="list-group-item" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_orders.php"><?php echo $lang['user_general_orders']; ?></a>
                    <?php } ?>
                    <a class="list-group-item" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_invoices.php"><?php echo $lang['user_general_invoices']; ?></a>
                    <a class="list-group-item" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_reviews.php"><?php echo $lang['user_general_reviews']; ?></a>
                    <?php if($this->checkPermission('user_advertiser')) { ?>
                        <a class="list-group-item" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_orders_add.php"><?php echo $lang['user_general_advertise']; ?></a>
                    <?php } ?>
                    <?php if($config['blog_user_posts'] AND ADDON_BLOG) { ?>
                        <a class="list-group-item" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_blog.php"><?php echo $lang['user_general_blog']; ?></a>
                    <?php } ?>
                    <?php if($contact_requests) { ?>
                        <a class="list-group-item" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_contact_requests.php"><?php echo $lang['user_general_contact_requests']; ?></a>
                        <a class="list-group-item" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_messages.php"><?php echo $lang['user_general_messages']; ?><span class="badge"><?php echo $messages_count; ?></span></a>
                    <?php } ?>
                <?php } ?>
                <a class="list-group-item" href="<?php echo BASE_URL_NOSSL; ?>"><?php echo $lang['user_general_directory_home']; ?></a>
                <?php if (LOGGED_IN) { ?>
                    <a class="list-group-item" href="<?php echo BASE_URL_NOSSL.MEMBERS_FOLDER; ?>user_index.php?action=logout"><?php echo $lang['user_general_logout']; ?></a>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-xl-9 col-lg-9 col-md-9 col-sm-8">
        <?php echo $message; ?>
        <?php echo $page_header; ?>
        <?php echo $template_content; ?>
    </div>
</div>
<?php echo $footer; ?>