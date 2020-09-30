<script type="text/javascript">
$(document).ready(function() {
    var checkOnComplete = function(data) {
        $("#status_percent").html(data.percent+"%");
        if(data.percent == 100) {
            setTimeout(function() {
                hideLoadingMessage();
                window.location.replace("<?php echo BASE_URL_ADMIN; ?>/admin_link_checker.php");
            },1000);
        } else {
            checkStart(data.start+data.num,data.num);
        }
    };

    var checkStart = function(start,num) {
        if(start == 0) {
            showLoadingMessage('Checking Links');
            $("#status_percent").html("0%");
        }
        $.ajax({ data: ({ action: "admin_link_checker_check", start: start, num: num }), success: checkOnComplete, dataType: "json"});
    };

    $("#link_checker_check").click(function(e) {
        e.preventDefault();
        checkStart(0,1);
    });

    <?php if(value($_GET,'action') == 'sort') { ?>
        $("#link_checker_check").trigger('click');
    <?php } ?>
});
</script>
<h1><?php echo $title; ?></h1>
<?php echo $content; ?>