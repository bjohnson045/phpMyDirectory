<script type="text/javascript">
var onComplete = function(data) {
    if(data == null) {
        $("#duplicate_content_results").html('<?php echo $lang['admin_maintenance_duplicates_no_results']; ?>');
    } else {
        $("#duplicate_content_results").append('<table id="duplicate_results_table" class="table table-bordered table-striped table-hover"><thead><tr><th>Data</th><th><?php echo $lang['admin_maintenance_duplicates_count']; ?></th></tr></thead></table>');
        $.each(data.results, function(i,record) {
            if(data.type == 'user_ip') {
                $("#duplicate_results_table").append('<tr><td><a href="<?php echo BASE_URL_ADMIN; ?>/admin_users.php?field=logged_ip&keyword='+record.logged_ip+'">'+record.logged_ip+'</a></td><td>'+record.count+'</td></tr>');
            } else if(data.type == 'user_name') {
                $("#duplicate_results_table").append('<tr><td><a href="<?php echo BASE_URL_ADMIN; ?>/admin_users.php?compare_type=equal&field=user_first_name&field2=user_last_name&keyword='+htmlspecialchars(record.user_first_name)+'&keyword2='+htmlspecialchars(record.user_last_name)+'">'+htmlspecialchars(record.user_first_name)+' '+htmlspecialchars(record.user_last_name)+'</a></td><td>'+record.count+'</td></tr>');
            } else if(data.type == 'user_phone') {
                $("#duplicate_results_table").append('<tr><td><a href="<?php echo BASE_URL_ADMIN; ?>/admin_users.php?field=user_phone&keyword='+htmlspecialchars(record.user_phone)+'">'+htmlspecialchars(record.user_phone)+'</a></td><td>'+record.count+'</td></tr>');
            } else if(data.type == 'listing_title') {
                $("#duplicate_results_table").append('<tr><td><a href="<?php echo BASE_URL_ADMIN; ?>/admin_listings.php?title='+htmlspecialchars(record.title)+'">'+htmlspecialchars(record.title)+'</a></td><td>'+record.count+'</td></tr>');
            } else if(data.type == 'listing_www') {
                $("#duplicate_results_table").append('<tr><td><a href="<?php echo BASE_URL_ADMIN; ?>/admin_listings.php?www='+htmlspecialchars(record.www)+'">'+htmlspecialchars(record.www)+'</a></td><td>'+record.count+'</td></tr>');
            } else if(data.type == 'listing_phone') {
                $("#duplicate_results_table").append('<tr><td><a href="<?php echo BASE_URL_ADMIN; ?>/admin_listings.php?phone='+htmlspecialchars(record.phone)+'">'+htmlspecialchars(record.phone)+'</a></td><td>'+record.count+'</td></tr>');
            }
        });
    }
    $("#loading_image").hide();
};

var showLoading = function(data) {
    $("#duplicate_content_results").empty();
    $("#loading_image").show();
};

var duplicatesCheck = function() {
    $.ajax({ data: ({ action: "admin_maintenance_duplicates", type: $(this).attr('id') }), beforeSend: showLoading, success: onComplete, dataType: "json"});
    return false;
};

$(document).ready(function(){
    $("#duplicate_links a").click(duplicatesCheck);
});
</script>
<h1><?php echo $lang['admin_maintenance_duplicates']; ?></h1>

<div id="duplicate_links" style="float: left; width: 150px;">
    <h2><?php echo $lang['admin_maintenance_duplicates_users']; ?></h2>
    <a id="user_ip" href="#"><?php echo $lang['admin_maintenance_duplicates_find_ip']; ?></a><br />
    <a id="user_name" href="#"><?php echo $lang['admin_maintenance_duplicates_find_name']; ?></a><br />
    <a id="user_phone" href="#"><?php echo $lang['admin_maintenance_duplicates_find_phone']; ?></a><br />
    <br /><br />
    <h2><?php echo $lang['admin_maintenance_duplicates_listings']; ?></h2>
    <a id="listing_title" href="#"><?php echo $lang['admin_maintenance_duplicates_find_title']; ?></a><br />
    <a id="listing_www" href="#"><?php echo $lang['admin_maintenance_duplicates_find_www']; ?></a><br />
    <a id="listing_phone" href="#"><?php echo $lang['admin_maintenance_duplicates_find_phone']; ?></a><br />
</div>

<div id="duplicate_content" style="margin-left: 200px;">
    <h2><?php echo $lang['admin_maintenance_duplicates_results']; ?></h2>
    <img id="loading_image" style="display: none;"src="<?php echo BASE_URL_ADMIN.TEMPLATE_PATH_ADMIN; ?>images/loading.gif"/>
    <div id="duplicate_content_results"><?php echo $lang['admin_maintenance_duplicates_no_results']; ?></div>
</div>