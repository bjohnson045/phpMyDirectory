<h3 style="margin: 0 0 0 0;"><?php echo $this->escape($title); ?></h3>
<?php if($listing_title) { ?>
    <p style="margin-top: 2px;"><?php echo $lang['public_classified_from']; ?> <a href="<?php echo $listing_url; ?>"><?php echo $this->escape($listing_title); ?></a></p>
<?php } ?>
<ul data-role="listview" data-inset="true">
    <li data-role="list-divider" role="heading"><?php echo $lang['public_classified_overview']; ?></li>
    <li>
        <?php if($description) { ?>
            <p style="white-space:normal"><?php echo $this->escape_html($description); ?></p>
        <?php } ?>
        <p><strong><?php echo $lang['public_classified_date']; ?>:</strong> <?php echo $this->escape($date); ?></p>
        <?php if($expire_date) { ?>
            <p><strong><?php echo $lang['public_classified_expire_date']; ?>:</strong> <?php echo $this->escape($expire_date); ?></p>
        <?php } ?>
        <?php if($price) { ?>
            <p><strong><?php echo $lang['public_classified_price']; ?>:</strong> <?php echo $this->escape($price); ?></p>
        <?php } ?>
    </li>
</ul>
<?php if($www OR $buy_url) { ?>
    <ul data-role="listview" data-inset="true">
        <li data-role="list-divider" role="heading"><?php echo $lang['public_classified_options']; ?></li>
        <?php if($www) { ?>
            <li><a target="_blank" href="<?php echo $this->escape($www); ?>"><?php echo $lang['public_classified_view']; ?></a></li>
        <?php } ?>
        <?php if($buy_url) { ?>
            <li><a target="_blank" href="<?php echo $this->escape($buy_url); ?>"><?php echo $lang['public_classified_buy']; ?></a></li>
        <?php } ?>
    </ul>
<?php } ?>
<?php if($other_classifieds) { ?>
    <h2><?php echo $lang['public_classified_other']; ?> <?php echo $this->escape($listing_title); ?></h2>
    <?php foreach($other_classifieds AS $classified) { ?>
        <a href="<?php echo $classified['url']; ?>"><?php echo $this->escape($classified['title']); ?></a><br />
    <?php } ?>
<?php } ?>
<?php if($classified_images) { ?>
    <ul data-role="listview" data-inset="true">
        <li data-role="list-divider" role="heading"><?php echo $lang['public_classified_images']; ?></li>
        <?php foreach($classified_images AS $key=>$image) { ?>
            <li>
                <a data-rel="popup" href="#image<?php echo $key; ?>" title="<?php echo $this->escape($title); ?>">
                    <img border="0" src="<?php echo $image['thumbnail_url']; ?>" />
                </a>
                <div data-role="popup" id="image<?php echo $key; ?>">
                    <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
                    <img src="<?php echo $image['image_url']; ?>" alt="<?php echo $this->escape($title); ?>">
                </div>
            </li>
        <?php } ?>
    </ul>
<?php } ?>