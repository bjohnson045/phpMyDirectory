<?php if($users_summary_header) { ?>
    <?php echo $users_summary_header; ?>
    <?php if($listing_header) { ?>
        <?php echo $listing_header; ?>
        <h3><?php echo $title ?></h3>
    <?php } else { ?>
        <h2><?php echo $title ?></h2>
    <?php } ?>
<?php } else { ?>
    <h1><?php echo $title ?></h1>
<?php } ?>
<?php echo $content; ?>