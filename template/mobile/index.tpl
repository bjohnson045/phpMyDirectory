<div class="ui-field-contain">
    <form action="search_results.php" method="get">
        <input type="search" name="keyword" id="keyword" value="" placeholder="Keyword" /><br />
        <input data-theme="a" data-mini="true" type="submit" name="submit" value="Search" />
    </form>
</div>
<ul data-role="listview" data-inset="true">
    <li><a href="browse_categories.php" data-prefetch="true">Categories</a></li>
    <li><a href="browse_locations.php" data-prefetch="true">Locations</a></li>
    <?php if(ADDON_BLOG) { ?>
        <li><a href="blog.php" data-prefetch="true">Blog</a></li>
    <?php } ?>
    <li><a href="faq.php" data-prefetch="true">Frequently Asked Questions</a></li>
    <li><a href="contact.php" data-prefetch="true">Contact Us</a></li>
</ul>
<h6 style="text-align: center"><a data-ajax="false" href="<?php echo BASE_URL; ?>/?template=default">View Full Version</a></h6>


