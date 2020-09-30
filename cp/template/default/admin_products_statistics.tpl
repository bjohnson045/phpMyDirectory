<h1><?php echo $title; ?></h1>
<?php if(!count($impressions) AND !count($counts)) { ?>
    <?php echo $lang['admin_products_no_statistics']; ?>
<?php } ?>
<?php if(count($impressions)) { ?>
    <script type="text/javascript">
    google.load('visualization', '1', {packages: ['corechart']});
    google.setOnLoadCallback(load_charts);
    function load_charts() {
        var data = google.visualization.arrayToDataTable([
        ['<?php echo $lang['admin_products_product']; ?>','<?php echo $lang['admin_products_impressions']; ?>'],
        <?php foreach($impressions AS $impression) { ?>
            ['<?php echo $impression['name']; ?>',<?php echo $impression['impressions']; ?>],
        <?php } ?>
        ]);
        var options = {
          title: '<?php echo $lang['admin_products_impressions']; ?>'
        };
        new google.visualization.PieChart(document.getElementById('product_impressions')).draw(data,options);
    }
    </script>
    <div id="product_impressions" style="width: 400px; height: 400px; float: left;"></div>
<?php } ?>
<?php if(count($counts)) { ?>
    <script type="text/javascript">
    google.load('visualization', '1', {packages: ['corechart']});
    google.setOnLoadCallback(load_charts);
    function load_charts() {
        var data = google.visualization.arrayToDataTable([
        ['<?php echo $lang['admin_products_product']; ?>','<?php echo $lang['admin_products_counts']; ?>'],
        <?php foreach($counts AS $count) { ?>
            ['<?php echo $count['name']; ?>',<?php echo $count['count']; ?>],
        <?php } ?>
        ]);
        var options = {
          title: '<?php echo $lang['admin_products_counts']; ?>'
        };
        new google.visualization.PieChart(document.getElementById('product_counts')).draw(data,options);
    }
    </script>
    <div id="product_counts" style="width: 400px; height: 400px; float: left;"></div>
<?php } ?>

