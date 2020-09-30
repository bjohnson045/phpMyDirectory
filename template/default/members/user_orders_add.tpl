<?php if($product_groups) { ?>
    <?php foreach($product_groups as $key=>$group) { ?>
    <h2><?php echo $this->escape($group['name']); ?></h2>
    <p><a class="btn btn-default" target="_blank" href="<?php echo BASE_URL; ?>/compare.php?group_id=<?php echo $key; ?>"><span class="fa fa-list-alt"></span> <?php echo $lang['user_orders_comparison_chart']; ?></a></p>
    <div class="row">
        <div class="col-lg-6 col-md-8 col-sm-10 col-xs-12">
        <?php foreach($group['products'] as $product) { ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $this->escape($product['name']); ?></h3>
                </div>
                <?php if($product['description'] != '') { ?>
                <div class="panel-body">
                    <p><?php echo $this->escape_html($product['description']); ?></p>
                </div>
                <?php } ?>
                <ul class="list-group">
                    <?php foreach($product['pricing'] as $price) { ?>
                        <li class="list-group-item">
                            <?php if($price['label']) { ?>
                                <h4><?php echo $this->escape($price['label']); ?></h4>
                            <?php } else { ?>
                                <?php if($price['period_count']) { ?>
                                    <?php echo $price['period_count']; ?> <?php echo $price['period']; ?>
                                <?php } else { ?>
                                    <?php echo $lang['user_orders_lifetime']; ?>
                                <?php } ?>
                                <?php if($price['price'] != '0.00') { ?>
                                    - <?php echo format_number_currency($price['price']); ?>
                                <?php } else { ?>
                                    - <?php echo $lang['user_orders_free']; ?>
                                <?php } ?>
                                <?php if($price['setup_price'] != '0.00') { ?>
                                    - <?php echo $lang['user_orders_setup']; ?>: <?php echo format_number_currency($price['setup_price']); ?>
                                <?php } ?>
                            <?php } ?>
                            <a class="btn btn-success btn-xs pull-right" href="<?php echo $price['url']; ?>"><?php echo $lang['select']; ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
<?php } else { ?>
    <?php echo $lang['user_orders_no_products']; ?>
<?php } ?>