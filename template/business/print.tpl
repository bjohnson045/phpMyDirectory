<div class="row">
    <div class="col-lg-12">
    <?php if($logo_url) { ?>
        <img src="<?php echo $logo_url; ?>" alt="<?php echo $this->escape($title); ?>" title="<?php echo $this->escape($title); ?>" /><br /><br />
    <?php } ?>
    <h1><?php echo $this->escape($title); ?></h1>
    <?php echo $description; ?>
    <?php echo $qrcode; ?>
    <p><?php echo $this->escape_html($address); ?></p>
    <p><?php echo $this->escape($phone); ?></p>
    <p><?php echo $this->escape($fax); ?></p>
    <?php echo $custom_fields; ?>
    <?php echo $map; ?>
    <p><?php echo $this->escape($www); ?></p>
    <p><a href="#" class="btn btn-default" onclick="window.print()"><span class="fa fa-print"></span> Print Page</a></p>
    <p><?php echo $listing_url; ?></p>
    </div>
</div>