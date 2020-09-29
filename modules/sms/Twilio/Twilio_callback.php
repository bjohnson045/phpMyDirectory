<?php
    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
    <Say>A customer at the number <?php echo $_GET['number_spaced']; ?> is calling</Say>
    <Dial><?php echo $_GET['number_formatted']; ?></Dial>
</Response>