<?php foreach($dates AS $date) { ?>
    <a href="blog.php?month=<?php echo $date['month_number']; ?>&year=<?php echo $date['year_number']; ?>"><?php echo $date['month']; ?>, <?php echo $date['year_number']; ?></a> (<?php echo $date['count']; ?>)<br />
<?php } ?>