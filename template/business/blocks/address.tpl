<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
    <?php if(!empty($address_line1)) { ?>
        <span itemprop="streetAddress">
            <?php echo $address_line1; ?><br>
            <?php if(!empty($address_line2)) { ?><?php echo $address_line2; ?><br><?php } ?>
        </span>
    <?php } ?>
    <?php if(!empty($city)) { ?><span itemprop="addressLocality"><?php echo $city; ?></span>,<?php } ?>
    <?php if(!empty($state)) { ?><span itemprop="addressRegion"><?php echo $state; ?></span><?php } ?>
    <?php if(!empty($zip)) { ?><span itemprop="postalCode"><?php echo $zip; ?></span><?php } ?>
    <?php if(!empty($country)) { ?><br><span itemprop="addressCountry"><?php echo $country; ?></span><?php } ?>
</div>