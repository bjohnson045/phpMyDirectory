<h1>User: <?php echo $name; ?></h1>
<div class="btn-toolbar">
    <div class="btn-group">
        <a class="btn btn-default<?php if($active == 'summary') { ?> active<?php } ?>" href="admin_users_summary.php?id=<?php echo $id; ?>"><i class="glyphicon glyphicon-user"></i> <?php echo $lang['admin_users_summary']; ?></a>
    </div>
    <div class="btn-group">
        <a class="btn btn-default<?php if($active == 'profile') { ?> active<?php } ?>" href="admin_users.php?action=edit&id=<?php echo $id; ?>"><i class="glyphicon glyphicon-pencil"></i> <?php echo $lang['admin_users_profile']; ?></a>
    </div>
    <div class="btn-group">
        <a class="btn btn-default<?php if($active == 'orders') { ?> active<?php } ?>" href="admin_orders.php?user_id=<?php echo $id; ?>"><i class="glyphicon glyphicon-shopping-cart"></i> <?php echo $lang['admin_users_orders']; ?></a>
        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="admin_orders_add.php?user_id=<?php echo $id; ?>">Add Order</a></li>
            <li><a href="admin_orders.php?action=search&user_id=<?php echo $id; ?>">Search Orders</a></li>
            <li><a href="admin_orders.php?status=active&user_id=<?php echo $id; ?>">Active Orders</a></li>
            <li><a href="admin_orders.php?status=pending&user_id=<?php echo $id; ?>">Pending Orders</a></li>
            <li><a href="admin_orders.php?status=suspended&user_id=<?php echo $id; ?>">Suspended Orders</a></li>
            <li><a href="admin_orders.php?status=canceled&user_id=<?php echo $id; ?>">Canceled Orders</a></li>
            <li><a href="admin_orders.php?status=fraud&user_id=<?php echo $id; ?>">Fraud Orders</a></li>
        </ul>
    </div>
    <div class="btn-group">
        <a class="btn btn-default<?php if($active == 'invoices') { ?> active<?php } ?>" href="admin_invoices.php?user_id=<?php echo $id; ?>"><i class="glyphicon glyphicon-file"></i> <?php echo $lang['admin_users_invoices']; ?></a>
        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="admin_invoices.php?action=add&user_id=<?php echo $id; ?>">Add Invoice</a></li>
            <li><a href="admin_invoices.php?action=search&user_id=<?php echo $id; ?>">Search Invoices</a></li>
        </ul>
    </div>
    <div class="btn-group">
        <a class="btn btn-default<?php if($active == 'transactions') { ?> active<?php } ?>" href="admin_transactions.php?user_id=<?php echo $id; ?>"><i class="glyphicon glyphicon-list"></i> <?php echo $lang['admin_users_transactions']; ?></a>
    </div>
    <div class="btn-group">
        <a class="btn btn-default<?php if($active == 'reviews') { ?> active<?php } ?>" href="admin_reviews.php?user_id=<?php echo $id; ?>"><i class="glyphicon glyphicon-comment"></i> <?php echo $lang['admin_users_reviews']; ?></a>
        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="admin_reviews.php?action=add&user_id=<?php echo $id; ?>">Add Review</a></li>
            <li><a href="admin_reviews.php?status=active&user_id=<?php echo $id; ?>">Active Reviews</a></li>
            <li><a href="admin_reviews.php?status=pending&user_id=<?php echo $id; ?>">Pending Reviews</a></li>
            <li><a href="admin_reviews_comments.php?&user_id=<?php echo $id; ?>">Comments</a></li>
            <li><a href="admin_reviews_comments.php?status=active&user_id=<?php echo $id; ?>">Active Comments</a></li>
            <li><a href="admin_reviews_comments.php?status=pending&user_id=<?php echo $id; ?>">Pending Comments</a></li>
        </ul>
    </div>
    <div class="btn-group">
        <a class="btn btn-default<?php if($active == 'ratings') { ?> active<?php } ?>" href="admin_ratings.php?user_id=<?php echo $id; ?>"><i class="glyphicon glyphicon-star"></i> <?php echo $lang['admin_users_ratings']; ?></a>
    </div>
    <?php if(ADDON_BLOG) { ?>
    <div class="btn-group">
        <a class="btn btn-default<?php if($active == 'blog_posts') { ?> active<?php } ?>" href="admin_blog.php?user_id=<?php echo $id; ?>"><i class="fa fa-file-text-o"></i> <?php echo $lang['admin_users_blog_posts']; ?></a>
        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="admin_blog.php?action=add&user_id=<?php echo $id; ?>">Add Blog Post</a></li>
            <li><a href="admin_blog.php?user_id=<?php echo $id; ?>">Blog Posts</a></li>
        </ul>
    </div>
    <?php } ?>
    <div class="btn-group">
        <a class="btn btn-default<?php if($active == 'email_log') { ?> active<?php } ?>" href="admin_email_log.php?user_id=<?php echo $id; ?>"><i class="glyphicon glyphicon-envelope"></i> <?php echo $lang['admin_users_emails']; ?></a>
    </div>
</div>