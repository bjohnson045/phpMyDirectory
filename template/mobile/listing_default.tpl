<?php if($logo_url) { ?>
    <img src="<?php echo $logo_url; ?>" alt="<?php echo $this->escape($title); ?>" title="<?php echo $this->escape($title); ?>" border="0" /><br /><br />
<?php } ?>
<h1><?php echo $this->escape($title); ?></h1>
<?php echo $this->escape($address); ?><br /><br />
<?php if($phone) { ?>
    <strong><?php echo $lang['public_listing_phone']; ?></strong>: <a href="tel:<?php echo $this->escape($phone); ?>"><?php echo $this->escape($phone); ?></a><br /><br />
<?php } ?>
<?php if($fax) { ?>
    <strong><?php echo $lang['public_listing_fax']; ?></strong>: <a href="tel:<?php echo $this->escape($fax); ?>"><?php echo $this->escape($fax); ?></a><br /><br />
<?php } ?>
<?php echo $description; ?>
<ul data-role="listview" style="margin-top: 15px">
    <li data-role="list-divider" role="heading">
        Options
    </li>
    <?php if($classifieds_count) { ?>
        <li><a href="<?php echo $classifieds_url; ?>"><?php echo $lang['public_listing_classifieds']; ?></a><span class="ui-li-count"><?php echo $classifieds_count; ?></span></li>
    <?php } ?>
    <?php if($images_count) { ?>
        <li><a href="<?php echo $images_url; ?>"><?php echo $lang['public_listing_images']; ?></a><span class="ui-li-count"><?php echo $images_count; ?></span></li>
    <?php } ?>
    <?php if($documents_count) { ?>
        <li><a href="<?php echo $documents_url; ?>"><?php echo $lang['public_listing_documents']; ?></a><span class="ui-li-count"><?php echo $documents_count; ?></span></li>
    <?php } ?>
    <?php if($skype_url) { ?>
        <li><a rel="nofollow" href="<?php echo $skype_url; ?>"><?php echo $lang['public_listing_skype']; ?></a></li>
    <?php } ?>
    <?php if($mail) { ?>
        <li><a href="<?php echo $mail; ?>"><?php echo $lang['public_listing_send_message']; ?></a></li>
    <?php } ?>
    <?php if($www_url) { ?>
        <li><a <?php echo $www_class;?> <?php echo $www_javascript; ?> href="<?php echo $this->escape($www_url); ?>" target="_blank" id="listing_www"><?php echo $lang['public_listing_www']; ?></a></li>
    <?php } ?>
    <!--
    <?php if($reviews_add_url) { ?>
        <li><a href="<?php echo $reviews_add_url; ?>"><?php echo $lang['public_listing_reviews_add']; ?></a></li>
    <?php } ?>
    -->
    <?php if($reviews_count) { ?>
        <li><a href="<?php echo $reviews_url; ?>"><?php echo $lang['public_listing_reviews']; ?></a><span class="ui-li-count"><?php echo $reviews_count; ?></span></li>
    <?php } ?>
    <?php if($print) { ?>
        <li><a rel="nofollow" href="#" onclick="window.print(); return false"><?php echo $lang['public_listing_print']; ?></a></li>
    <?php } ?>
    <?php if($email_friend) { ?>
        <li><a href="<?php echo $email_friend; ?>"><?php echo $lang['public_listing_email_friend']; ?></a></li>
    <?php } ?>
    <?php if($pdf_url) { ?>
        <li><a data-ajax="false" rel="nofollow" href="<?php echo $pdf_url; ?>" target="_blank"><?php echo $lang['public_listing_pdf_download']; ?></a></li>
    <?php } ?>
    <!--
    <?php if($vcard_url) { ?>
        <li><a rel="nofollow" href="<?php echo $vcard_url; ?>" target="_blank"><?php echo $lang['public_listing_vcard']; ?></a></li>
    <?php } ?>
    <?php if($favorites_url) { ?>
        <li><a href="<?php echo $favorites_url; ?>"><?php echo $favorites_text; ?></a></li>
    <?php } ?>
    <?php if($suggestion_url) { ?>
        <li><a href="<?php echo $suggestion_url; ?>"><?php echo $lang['public_listing_suggestion']; ?></a></li>
    <?php } ?>
    -->
    <?php if($report_url) { ?>
        <li><a href="<?php echo $report_url; ?>"><?php echo $lang['public_listing_report']; ?></a></li>
    <?php } ?>
</ul>