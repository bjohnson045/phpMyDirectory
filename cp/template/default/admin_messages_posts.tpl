<h1><?php echo $lang['admin_messages_message_posts']; ?></h1>
<h2><?php echo $lang['admin_messages_message']; ?></h2>
<p><?php echo $lang['admin_messages_title']; ?>: <?php echo $message['title']; ?></p>
<p><?php echo $lang['admin_messages_from']; ?>: <a href="admin_users_summary.php?id=<?php echo $message['user_id_from']; ?>"><?php echo $message['user_from']; ?></a></p>
<p><?php echo $lang['admin_messages_to']; ?>: <a href="admin_users_summary.php?id=<?php echo $message['user_id_to']; ?>"><?php echo $message['user_to']; ?></a></p>
<p><?php echo $lang['admin_messages_date_sent']; ?>: <?php echo $message['date_sent']; ?></p>

<?php if(!empty($message_posts)) { ?>
    <h2><?php echo $lang['admin_messages_posts']; ?></h2>
    <?php foreach($message_posts AS $post) { ?>
        <?php if($post['user_id'] == $message['user_id_from']) { ?>
            <div class="well well-small" style="overflow: hidden; position: relative;">
                <a class="close" style="position: absolute; right: 9px" href="admin_messages_posts.php?action=delete&id=<?php echo $post['id']; ?>&message_id=<?php echo $message['id']; ?>">&times;</a>
                <blockquote class="pull-left" style="margin-right: 20px;">
                    <p><?php echo $post['content']; ?></p>
                    <small><?php echo $post['user']; ?> - <?php echo $post['date_sent']; ?></small>
                </blockquote>
            </div>
        <?php } else { ?>
            <div class="well well-small" style="overflow: hidden; position: relative;">
                <a class="close pull-left" style="position: absolute" href="admin_messages_posts.php?action=delete&id=<?php echo $post['id']; ?>&message_id=<?php echo $message['id']; ?>">&times;</a>
                <blockquote class="pull-right" style="margin-left: 20px;">
                    <p><?php echo $post['content']; ?></p>
                    <small><?php echo $post['user']; ?> - <?php echo $post['date_sent']; ?></small>
                </blockquote>
            </div>
        <?php } ?>
    <?php } ?>
<?php } ?>