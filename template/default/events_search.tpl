<ul class="nav nav-tabs">
    <li><a href="events_calendar.php"><?php echo $lang['public_events_calendar']; ?></a></li>
    <li><a href="events_list.php"><?php echo $lang['public_events_list']; ?></a></li>
    <li class="active"><a href="events_search.php"><?php echo $lang['public_events_search']; ?></a></li>
    <li><a href="events_map.php"><?php echo $lang['public_events_map']; ?></a></li>
</ul>
<div class="row">
    <div class="col-lg-12">
        <?php echo $form->getFormOpenHTML(array('class'=>'form-inline')); ?>
            <?php echo $form->getFieldHTML('keyword'); ?>
            <?php echo $form->getFieldHTML('location'); ?>
            <?php echo $form->getFieldHTML('category_id'); ?>
            <?php echo $form->getFieldHTML('submit'); ?>
        <?php echo $form->getFormCloseHTML(); ?>
    </div>
</div>
<br>
<?php if($events_count) { ?>
    <?php echo $events_results; ?>
<?php } else { ?>
    <?php echo $lang['public_search_results_no_results']; ?>
<?php } ?>