<?php
include('../../../defaults.php');
$url_query_string = http_build_query($_POST);
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <script>
        if(window != top) {
            top.location.replace('<?php echo BASE_URL.MEMBERS_FOLDER.'user_payment_return.php?'.$url_query_string; ?>');
        } else {
            location.replace('<?php echo BASE_URL.MEMBERS_FOLDER.'user_payment_return.php?'.$url_query_string; ?>');
        }
        </script>
    </head>
    <body>
    Please wait..
    </body>
</html>