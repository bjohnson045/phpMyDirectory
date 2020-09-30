<a class="list-group-item" href="./admin_settings.php?group=general"><?php echo $lang['setting_group_general']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=categories"><?php echo $lang['setting_group_categories']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=locations"><?php echo $lang['setting_group_locations']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=users">Users</a>
<a class="list-group-item" href="./admin_settings.php?group=listings"><?php echo $lang['listings']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=search"><?php echo $lang['setting_group_search']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=email"><?php echo $lang['setting_group_email']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=invoicing"><?php echo $lang['setting_group_invoicing']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=reviews"><?php echo $lang['setting_group_reviews']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=logos"><?php echo $lang['setting_group_logos']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=classifieds"><?php echo $lang['setting_group_classifieds']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=events"><?php echo $lang['setting_group_events']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=jobs"><?php echo $lang['setting_group_jobs']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=gallery"><?php echo $lang['setting_group_gallery']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=documents"><?php echo $lang['setting_group_documents']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=banners"><?php echo $lang['setting_group_banners']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=caching"><?php echo $lang['setting_group_caching']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=blocks">Blocks</a>
<?php if(ADDON_BLOG) { ?>
    <a class="list-group-item" href="./admin_settings.php?group=blog"><?php echo $lang['setting_group_blog']; ?></a>
<?php } ?>
<a class="list-group-item" href="./admin_settings.php?group=website_screenshots"><?php echo $lang['setting_group_website_screenshots']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=statistics"><?php echo $lang['setting_group_statistics']; ?></a>
<a class="list-group-item" href="./admin_settings.php?group=other"><?php echo $lang['setting_group_other']; ?></a>
<?php if($custom) { ?>
    <a class="list-group-item" href="./admin_settings.php?group=custom"><?php echo $lang['setting_group_custom']; ?></a>
<?php } ?>