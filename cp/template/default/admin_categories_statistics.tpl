<h1><?php echo $title; ?></h1>
<a class="btn btn-default" href="admin_categories_statistics.php?action=download"><?php echo $lang['admin_categories_download_impressions']; ?></a>
<h2><?php echo $lang['admin_categories_graphs']; ?></h2>
<?php if(count($impressions)) { ?>
    <script type="text/javascript">
    google.load('visualization', '1', {packages: ['corechart']});
    google.setOnLoadCallback(load_charts);
    function load_charts() {
        var data = google.visualization.arrayToDataTable([
        ['<?php echo $lang['admin_categories_category']; ?>','<?php echo $lang['admin_categories_impressions']; ?>'],
        <?php foreach($impressions AS $impression) { ?>
            ['<?php echo $impression['title']; ?>',<?php echo $impression['impressions']; ?>],
        <?php } ?>
        ]);
        var options = {
          title: '<?php echo $lang['admin_categories_top_impressions']; ?>'
        };
        new google.visualization.PieChart(document.getElementById('category_impressions')).draw(data,options);
    }
    </script>
    <div id="category_impressions" style="width: 400px; height: 400px; float: left;"></div>
<?php } ?>
<?php if(count($impressions_search)) { ?>
    <script type="text/javascript">
    google.load('visualization', '1', {packages: ['corechart']});
    google.setOnLoadCallback(load_charts);
    function load_charts() {
        var data = google.visualization.arrayToDataTable([
        ['<?php echo $lang['admin_categories_category']; ?>','<?php echo $lang['admin_categories_impressions_search']; ?>'],
        <?php foreach($impressions_search AS $impression) { ?>
            ['<?php echo $impression['title']; ?>',<?php echo $impression['impressions_search']; ?>],
        <?php } ?>
        ]);
        var options = {
          title: '<?php echo $lang['admin_categories_top_impressions_search']; ?>'
        };
        new google.visualization.PieChart(document.getElementById('category_impressions_search')).draw(data,options);
    }
    </script>
    <div id="category_impressions_search" style="width: 400px; height: 400px; float: left;"></div>
<?php } ?>
<?php if(count($counts)) { ?>
    <script type="text/javascript">
    google.load('visualization', '1', {packages: ['corechart']});
    google.setOnLoadCallback(load_charts);
    function load_charts() {
        var data = google.visualization.arrayToDataTable([
        ['<?php echo $lang['admin_categories_category']; ?>','<?php echo $lang['admin_categories_count_total']; ?>'],
        <?php foreach($counts AS $count) { ?>
            ['<?php echo $count['title']; ?>',<?php echo $count['count']; ?>],
        <?php } ?>
        ]);
        var options = {
          title: '<?php echo $lang['admin_categories_top_counts']; ?>'
        };
        new google.visualization.PieChart(document.getElementById('category_counts')).draw(data,options);
    }
    </script>
    <div id="category_counts" style="width: 400px; height: 400px; float: left;"></div>
<?php } ?>

