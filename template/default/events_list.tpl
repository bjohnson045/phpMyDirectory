<ul class="nav nav-tabs">
    <li><a href="events_calendar.php"><?php echo $lang['public_events_calendar']; ?></a></li>
    <li class="active"><a href="events_list.php"><?php echo $lang['public_events_list']; ?></a></li>
    <li><a href="events_search.php"><?php echo $lang['public_events_search']; ?></a></li>
    <li><a href="events_map.php"><?php echo $lang['public_events_map']; ?></a></li>
</ul>
<?php foreach($events_list AS $event) { ?>
    <p><?php echo $event['date_start']; ?> - <a href="<?php echo $event['url']; ?>"><?php echo $event['title']; ?></a></p>
<?php } ?>
<div class="text-center">
    <?php echo $page_navigation; ?>
</div>