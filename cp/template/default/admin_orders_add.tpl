<?php if($users_summary_header) { ?>
    <?php echo $users_summary_header; ?>
    <h2><?php echo $title ?></h2>
<?php } else { ?>
    <h1><?php echo $title ?></h1>
<?php } ?>
<?php echo $content; ?>