<script type="text/javascript">
$(document).ready(function() {
    $("#date_range_link").click(function() {
        $("#date_range").show();
    });
});
</script>
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
<div class="btn-group">
    <a class="btn btn-default btn-sm<?php if($_GET['type']== 'today') { ?> active<?php } ?>" href="<?php echo rebuild_url(array('type'=>'today')); ?>"><?php echo $lang['admin_listings_statistics_today']; ?></a>
    <a class="btn btn-default btn-sm<?php if($_GET['type']== 'yesterday') { ?> active<?php } ?>" href="<?php echo rebuild_url(array('type'=>'yesterday')); ?>"><?php echo $lang['admin_listings_statistics_yesterday']; ?></a>
    <a class="btn btn-default btn-sm<?php if($_GET['type']== 'last_7') { ?> active<?php } ?>" href="<?php echo rebuild_url(array('type'=>'last_7')); ?>"><?php echo $lang['admin_listings_statistics_last_7']; ?></a>
    <a class="btn btn-default btn-sm<?php if($_GET['type']== 'last_30') { ?> active<?php } ?>" href="<?php echo rebuild_url(array('type'=>'last_30')); ?>"><?php echo $lang['admin_listings_statistics_last_30']; ?></a>
    <a class="btn btn-default btn-sm<?php if($_GET['type']== 'this_month') { ?> active<?php } ?>" href="<?php echo rebuild_url(array('type'=>'this_month')); ?>"><?php echo $lang['admin_listings_statistics_this_month']; ?></a>
    <a class="btn btn-default btn-sm<?php if($_GET['type']== 'last_month') { ?> active<?php } ?>" href="<?php echo rebuild_url(array('type'=>'last_month')); ?>"><?php echo $lang['admin_listings_statistics_last_month']; ?></a>
    <a class="btn btn-default btn-sm<?php if($_GET['type']== 'this_year') { ?> active<?php } ?>" href="<?php echo rebuild_url(array('type'=>'this_year')); ?>"><?php echo $lang['admin_listings_statistics_this_year']; ?></a>
    <a class="btn btn-default btn-sm<?php if($_GET['type']== 'last_year') { ?> active<?php } ?>" href="<?php echo rebuild_url(array('type'=>'last_year')); ?>"><?php echo $lang['admin_listings_statistics_last_year']; ?></a>
    <a class="btn btn-default btn-sm<?php if($_GET['type']== 'all_time') { ?> active<?php } ?>" href="<?php echo rebuild_url(array('type'=>'all_time')); ?>"><?php echo $lang['admin_listings_statistics_all_time']; ?></a>
    <a class="btn btn-default btn-sm" id="date_range_link" href="#"><?php echo $lang['admin_listings_statistics_date_range']; ?></a>
</div>
<a class="btn btn-default btn-sm" href="<?php echo $send_statistics_url; ?>"><i class="fa fa-envelope-o"></i> <?php echo $lang['admin_listings_statistics_send_email']; ?></a>

<br /><br />
<div id="date_range" style="display: none">
    <?php echo $range_form->getFormOpenHTML(array('class'=>'form-inline')); ?>
    <?php echo $range_form->getFieldHTML('date_start'); ?> - <?php echo $range_form->getFieldHTML('date_end'); ?> <?php echo $range_form->getFieldHTML('submit'); ?><br /><br />
    <?php echo $range_form->getFormCloseHTML(); ?>
</div>
<h2><?php echo $subtitle; ?></h2>
<?php if($statistics_days OR $statistics) { ?>
    <script type="text/javascript">
    google.load('visualization', '1', {packages: ['corechart']});

    function load_charts() {
        <?php if($group_type == 'daily') { ?>
            var data = google.visualization.arrayToDataTable([
            ['x','<?php echo $lang['admin_listings_statistics_impressions']; ?>','<?php echo $lang['admin_listings_statistics_search_impressions']; ?>', '<?php echo $lang['admin_listings_statistics_website_clicks']; ?>', '<?php echo $lang['admin_listings_statistics_emails']; ?>', '<?php echo $lang['admin_listings_statistics_banner_impressions']; ?>', '<?php echo $lang['admin_listings_statistics_banner_clicks']; ?>', '<?php echo $lang['admin_listings_statistics_share']; ?>', '<?php echo $lang['admin_listings_statistics_email_view']; ?>', '<?php echo $lang['admin_listings_statistics_phone_view']; ?>'],
            ['',0,0,0,0,0,0,0,0,0],
            <?php foreach($statistics AS $statistic_date => $statistic) { ?>
                ['<?php echo $PMDR->get('Dates')->formatDate($statistic_date); ?>',<?php echo $statistic['listing_impression']; ?>,<?php echo $statistic['listing_search_impression']; ?>,<?php echo $statistic['listing_website']; ?>,<?php echo $statistic['listing_email']; ?>,<?php echo $statistic['listing_banner_impression']; ?>,<?php echo $statistic['listing_banner_click']; ?>,<?php echo $statistic['listing_share']; ?>,<?php echo $statistic['listing_phone_view']; ?>,<?php echo $statistic['listing_email_view']; ?>],
            <?php } ?>
            ]);
            // Create and draw the visualization.
            new google.visualization.LineChart(document.getElementById('visualization')).draw(data,{width:700, height:400,vAxis: {title: "<?php echo $lang['admin_listings_statistics_count']; ?>"},hAxis: {title: "<?php echo $lang['admin_listings_statistics_day']; ?>"}});
        <?php } else { ?>
            var data = google.visualization.arrayToDataTable([
            ['<?php echo $lang['admin_listings_statistics_month']; ?>','<?php echo $lang['admin_listings_statistics_impressions']; ?>','<?php echo $lang['admin_listings_statistics_search_impressions']; ?>', '<?php echo $lang['admin_listings_statistics_website_clicks']; ?>', '<?php echo $lang['admin_listings_statistics_emails']; ?>', '<?php echo $lang['admin_listings_statistics_banner_impressions']; ?>', '<?php echo $lang['admin_listings_statistics_banner_clicks']; ?>', '<?php echo $lang['admin_listings_statistics_share']; ?>', '<?php echo $lang['admin_listings_statistics_email_view']; ?>', '<?php echo $lang['admin_listings_statistics_phone_view']; ?>'],
            <?php foreach($statistics AS $statistic_year => $statistic_months) { ?>
                <?php foreach($statistic_months AS $statistic_month => $statistic) { ?>
                    <?php if($statistic_year == 0) { ?>
                        ['Archived',<?php echo $statistic['listing_impression']; ?>,<?php echo $statistic['listing_search_impression']; ?>,<?php echo $statistic['listing_website']; ?>,<?php echo $statistic['listing_email']; ?>,<?php echo $statistic['listing_banner_impression']; ?>,<?php echo $statistic['listing_banner_click']; ?>,<?php echo $statistic['listing_share']; ?>,<?php echo $statistic['listing_phone_view']; ?>,<?php echo $statistic['listing_email_view']; ?>],
                    <?php } else { ?>
                        ['<?php echo strftime('%B',mktime(0, 0, 0, $statistic_month)); ?>, <?php echo $statistic_year; ?>',<?php echo $statistic['listing_impression']; ?>,<?php echo $statistic['listing_search_impression']; ?>,<?php echo $statistic['listing_website']; ?>,<?php echo $statistic['listing_email']; ?>,<?php echo $statistic['listing_banner_impression']; ?>,<?php echo $statistic['listing_banner_click']; ?>,<?php echo $statistic['listing_share']; ?>,<?php echo $statistic['listing_phone_view']; ?>,<?php echo $statistic['listing_email_view']; ?>],
                    <?php } ?>
                <?php } ?>
            <?php } ?>
            ]);
            // Create and draw the visualization.
            new google.visualization.BarChart(document.getElementById('visualization')).draw(data,{width:700, height:400,vAxis: {title: "<?php echo $lang['admin_listings_statistics_month']; ?>"},hAxis: {title: "<?php echo $lang['admin_listings_statistics_count']; ?>"}});
        <?php } ?>
    }
    google.setOnLoadCallback(load_charts);
    </script>
    <div role="tabpanel">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#graph" aria-controls="graph" role="tab" data-toggle="tab">Graph</a></li>
            <li role="presentation"><a href="#list" aria-controls="list" role="tab" data-toggle="tab">List</a></li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="graph">
                <div id="visualization" style="width: 700px; height: 400px;"></div>
            </div>
            <div role="tabpanel" class="tab-pane" id="list">
            <?php if($group_type == 'daily') { ?>
                <table class="table table-bordered" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Date / Type</th>
                            <th><?php echo $lang['admin_listings_statistics_month']; ?></th>
                            <th><?php echo $lang['admin_listings_statistics_search_impressions']; ?></th>
                            <th><?php echo $lang['admin_listings_statistics_website_clicks']; ?></th>
                            <th><?php echo $lang['admin_listings_statistics_emails']; ?></th>
                            <th><?php echo $lang['admin_listings_statistics_banner_impressions']; ?></th>
                            <th><?php echo $lang['admin_listings_statistics_banner_clicks']; ?></th>
                            <th><?php echo $lang['admin_listings_statistics_share']; ?></th>
                            <th><?php echo $lang['admin_listings_statistics_phone_view']; ?></th>
                            <th><?php echo $lang['admin_listings_statistics_email_view']; ?></th>
                        <tr>
                    </thead>
                    <tbody>
                    <?php foreach($statistics AS $statistic_date => $statistic) { ?>
                        <tr>
                            <th scope="row"><?php echo $statistic_date; ?></th>
                            <td><?php echo $statistic['listing_impression']; ?></td>
                            <td><?php echo $statistic['listing_search_impression']; ?></td>
                            <td><?php echo $statistic['listing_website']; ?></td>
                            <td><?php echo $statistic['listing_email']; ?></td>
                            <td><?php echo $statistic['listing_banner_impression']; ?></td>
                            <td><?php echo $statistic['listing_banner_click']; ?></td>
                            <td><?php echo $statistic['listing_share']; ?></td>
                            <td><?php echo $statistic['listing_phone_view']; ?></td>
                            <td><?php echo $statistic['listing_email_view']; ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <?php foreach($statistics AS $statistic_year => $statistic_months) { ?>
                    <h3><?php if($statistic_year == 0) { ?>Archived<?php } else { echo $statistic_year; } ?></h3>
                    <table class="table table-bordered" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>Date / Type</th>
                                <th><?php echo $lang['admin_listings_statistics_impressions']; ?></th>
                                <th><?php echo $lang['admin_listings_statistics_search_impressions']; ?></th>
                                <th><?php echo $lang['admin_listings_statistics_website_clicks']; ?></th>
                                <th><?php echo $lang['admin_listings_statistics_emails']; ?></th>
                                <th><?php echo $lang['admin_listings_statistics_banner_impressions']; ?></th>
                                <th><?php echo $lang['admin_listings_statistics_banner_clicks']; ?></th>
                                <th><?php echo $lang['admin_listings_statistics_share']; ?></th>
                                <th><?php echo $lang['admin_listings_statistics_phone_view']; ?></th>
                                <th><?php echo $lang['admin_listings_statistics_email_view']; ?></th>
                            <tr>
                        </thead>
                        <tbody>
                        <?php foreach($statistic_months AS $statistic_month => $statistic) { ?>
                            <tr>
                                <th scope="row"><?php echo strftime('%B',mktime(0, 0, 0, $statistic_month)); ?></th>
                                <td><?php echo $statistic['listing_impression']; ?></td>
                                <td><?php echo $statistic['listing_search_impression']; ?></td>
                                <td><?php echo $statistic['listing_website']; ?></td>
                                <td><?php echo $statistic['listing_email']; ?></td>
                                <td><?php echo $statistic['listing_banner_impression']; ?></td>
                                <td><?php echo $statistic['listing_banner_click']; ?></td>
                                <td><?php echo $statistic['listing_share']; ?></td>
                                <td><?php echo $statistic['listing_phone_view']; ?></td>
                                <td><?php echo $statistic['listing_email_view']; ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            <?php } ?>
            </div>
        </div>
    </div>
<?php } else { ?>
    <?php echo $lang['admin_listings_statistics_none']; ?>
<?php } ?>