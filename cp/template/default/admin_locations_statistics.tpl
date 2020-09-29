<h1><?php echo $title; ?></h1>
<a class="btn btn-default" href="admin_locations_statistics.php?action=download"><?php echo $lang['admin_locations_download_impressions']; ?></a>
<h2><?php echo $lang['admin_locations_graphs']; ?></h2>
<?php if(count($impressions)) { ?>
    <script type="text/javascript">
    google.load('visualization', '1', {packages: ['corechart']});
    google.setOnLoadCallback(load_charts);
    function load_charts() {
        var data = google.visualization.arrayToDataTable([
        ['<?php echo $lang['admin_locations_location']; ?>','<?php echo $lang['admin_locations_impressions']; ?>'],
        <?php foreach($impressions AS $impression) { ?>
            ['<?php echo $impression['title']; ?>',<?php echo $impression['impressions']; ?>],
        <?php } ?>
        ]);
        var options = {
            title: '<?php echo $lang['Top 10 Locations by Impressions']; ?>'
        };
        new google.visualization.PieChart(document.getElementById('location_impressions')).draw(data,options);
    }
    </script>
    <div id="location_impressions" style="width: 400px; height: 400px; float: left;"></div>
<?php } ?>
<?php if(count($impressions_search)) { ?>
    <script type="text/javascript">
    google.load('visualization', '1', {packages: ['corechart']});
    google.setOnLoadCallback(load_charts);
    function load_charts() {
        var data = google.visualization.arrayToDataTable([
        ['<?php echo $lang['admin_locations_location']; ?>','<?php echo $lang['admin_locations_impressions_search']; ?>'],
        <?php foreach($impressions_search AS $impression) { ?>
            ['<?php echo $impression['title']; ?>',<?php echo $impression['impressions_search']; ?>],
        <?php } ?>
        ]);
        var options = {
            title: '<?php echo $lang['admin_locations_top_impressions_search']; ?>'
        };
        new google.visualization.PieChart(document.getElementById('location_impressions_search')).draw(data,options);
    }
    </script>
    <div id="location_impressions_search" style="width: 400px; height: 400px; float: left;"></div>
<?php } ?>
<?php if(count($counts)) { ?>
    <script type="text/javascript">
    google.load('visualization', '1', {packages: ['corechart']});
    google.setOnLoadCallback(load_charts);
    function load_charts() {
        var data = google.visualization.arrayToDataTable([
        ['<?php echo $lang['admin_locations_location']; ?>','<?php echo $lang['admin_locations_count_total']; ?>'],
        <?php foreach($counts AS $count) { ?>
            ['<?php echo $count['title']; ?>',<?php echo $count['count']; ?>],
        <?php } ?>
        ]);
        var options = {
            title: '<?php echo $lang['admin_locations_top_counts']; ?>'
        };
        new google.visualization.PieChart(document.getElementById('location_counts')).draw(data,options);
    }
    </script>
    <div id="location_counts" style="width: 400px; height: 400px; float: left;"></div>
<?php } ?>

