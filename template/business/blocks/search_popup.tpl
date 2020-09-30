<?php if($results) { ?>
    <?php if($listings) { ?>
        <p>
            <strong><?php echo $lang['public_general_search_listings']; ?></strong><br />
            <?php foreach($listings AS $listing) { ?>
                <a href="<?php echo $listing['url']; ?>"><?php echo $listing['title']; ?></a><br />
            <?php } ?>
        </p>
    <?php } ?>
    <?php if($classifieds) { ?>
        <p>
            <strong><?php echo $lang['public_general_search_classifieds']; ?></strong><br />
            <?php foreach($classifieds AS $classified) { ?>
                <a href="<?php echo $classified['url']; ?>"><?php echo $classified['title']; ?></a><br />
            <?php } ?>
        </p>
    <?php } ?>
    <?php if($categories) { ?>
        <p>
            <strong><?php echo $lang['public_general_search_categories']; ?></strong><br />
            <?php foreach($categories AS $category) { ?>
                <a href="<?php echo $category['url']; ?>"><?php echo $category['title']; ?></a><br />
            <?php } ?>
        </p>
    <?php } ?>
<?php } else { ?>
    <?php echo $lang['public_general_search_no_results']; ?>
<?php } ?>