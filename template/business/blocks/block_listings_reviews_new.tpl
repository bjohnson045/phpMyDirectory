<?php if(count($results)) { ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['block_listings_reviews_new']; ?><a class="pull-right" href="<?php echo BASE_URL; ?>/xml.php?type=rss_listings_reviews_new"><i class="fa fa-rss"></i></a></h3>
    </div>
    <ul class="list-group">
    <?php foreach($results as $result) { ?>
        <li class="list-group-item">
            <div class="pull-right"><?php echo $result['rating_static']; ?></div>
            <h5 class="list-group-item-heading"><a href="<?php echo $result['listing_url']; ?>"><?php echo $this->escape($result['listing_title']); ?></a></h5>
            <p class="list-group-item-text text-muted"><small><?php echo $this->escape($result['title']); ?></small></p>
            <span class="tiny"><?php echo $result['date']; ?></span>
        </li>
    <?php } ?>
    </ul>
</div>
<?php } ?>