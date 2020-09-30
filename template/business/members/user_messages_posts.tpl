<p><?php echo $lang['user_messages_title']; ?>: <?php echo $this->escape($message['title']); ?></p>
<p><?php echo $lang['user_messages_from']; ?>: <?php echo $this->escape($message['user_from']); ?></p>
<p><?php echo $lang['user_messages_to']; ?>: <?php echo $this->escape($message['user_to']); ?></p>

<h3><?php echo $lang['user_messages_reply']; ?></h3>
<?php echo $form->getFormOpenHTML(); ?>
<?php echo $form->getFieldGroup('content'); ?>
<?php echo $form->getFormActions(); ?>
<?php echo $form->getFormCloseHTML(); ?>

<?php if(!empty($message_posts)) { ?>
    <h3><?php echo $lang['user_messages_posts']; ?></h3>
    <?php foreach($message_posts AS $key=>$post) { ?>
        <?php if($post['user_id'] == $message['user_id_from']) { ?>
            <blockquote class="pull-right">
                <p><?php echo $this->escape($post['content']); ?></p>
                <small><?php echo $this->escape($post['user']); ?> on <?php echo $post['date_sent']; ?></small>
            </blockquote>
        <?php } else { ?>
            <blockquote>
                <p><?php echo $this->escape($post['content']); ?></p>
                <small><?php echo $this->escape($post['user']); ?> on <?php echo $post['date_sent']; ?></small>
            </blockquote>
        <?php } ?>
    <?php } ?>
<?php } ?>