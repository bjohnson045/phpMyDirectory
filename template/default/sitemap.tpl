<script type="text/javascript">
$(document).ready(function() {
    $("#sitemap_tree_categories, #sitemap_tree_locations").dynatree({
        onActivate: function(node) {
            if(node.data.href) {
                window.location.href = node.data.href;
            }
        }
    });
});
</script>
<?php echo $links; ?>
<?php if($sitemap_categories) { ?>
    <h2><?php echo $lang['public_sitemap_categories']; ?></h2>
    <div id="sitemap_tree_categories" class="tree_select_expanding_wrapper">
        <?php echo $sitemap_categories; ?>
    </div>
<?php } ?>
<?php if($sitemap_locations) { ?>
    <h2><?php echo $lang['public_sitemap_locations']; ?></h2>
    <div id="sitemap_tree_locations" class="tree_select_expanding_wrapper">
        <?php echo $sitemap_locations; ?>
    </div>
<?php } ?>