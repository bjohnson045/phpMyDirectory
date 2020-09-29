<h1><?php echo $lang['admin_search_log']; ?></h1>
<?php if($form_search) { ?>
    <script type="text/javascript">
    $(document).ready(function() {
        <?php if($_GET['action'] == 'search') { ?>
            $("#search_log_search_container").slideToggle();
        <?php } ?>
        $("#search_log_search_container .close").click(function() {
            $("#search_log_search_container").slideToggle();
            return false;
        });
        $("#search_log_search_link").click(function() {
            $("#search_log_search_container").slideToggle();
            return false;
        });
    });
    </script>
    <div id="search_log_search_container" class="panel panel-default" style="display: none; margin-top: 20px;">
    <div class="panel-heading"><?php echo $lang['admin_search_log_search']; ?><button type="button" class="close">×</button></div>
        <div class="panel-body">
            <?php echo $form_search->getFormOpenHTML(array('class'=>'form-horizontal')); ?>
            <div class="row">
                <div class="col-md-9">
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('keywords'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('keywords'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('results_found'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('results_found'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-offset-4 col-lg-10">
                            <?php echo $form_search->getFieldHTML('submit'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('date_start'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('date_start'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('date_end'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('date_end'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo $form_search->getFormCloseHTML(); ?>
        </div>
    </div>
<?php } ?>
<?php if($_GET['action'] == 'popular') { ?>
    <ul class="nav nav-tabs" id="myTab">
        <li class="active"><a href="#popular"><?php echo $lang['admin_search_log_popular']; ?></a></li>
        <li><a href="#graph"><?php echo $lang['admin_search_log_top_keywords']; ?></a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="popular"><?php echo $content; ?></div>
        <div class="tab-pane" id="graph">
            <script type="text/javascript">
                google.load('visualization', '1', {packages: ['corechart']});
                google.setOnLoadCallback(load_charts);
                function load_charts() {
                    var data = google.visualization.arrayToDataTable([
                      ['<?php echo $lang['admin_search_log_keyword']; ?>', '<?php echo $lang['admin_search_log_count']; ?>'],
                      <?php foreach($records AS $record) { ?>
                            ['<?php echo $record['keywords']; ?>',  <?php echo $record['count']; ?>],
                      <?php } ?>
                    ]);
                    var options = {
                        title: '<?php echo $lang['admin_search_log_top_keywords']; ?>',
                        vAxis: {title: '<?php echo $lang['admin_search_log_keyword']; ?>'},
                        hAxis: {title: '<?php echo $lang['admin_search_log_count']; ?>'},
                        height: 600,
                        width: 800
                    };
                    new google.visualization.BarChart(document.getElementById('search_log')).draw(data,options);
                }
                </script>
                <div id="search_log" style="width: 700px; height: 700px; float: left;"></div>
            </div>
    </div>
    <script>
    $('#myTab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    })
    </script>
<?php } else { ?>
    <?php echo $content; ?>
<?php } ?>