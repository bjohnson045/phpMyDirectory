<?php if(!$memberships) { ?>
    <?php echo $lang['public_compare_no_products']; ?>
<?php } else { ?>
    <?php if(!LOGGED_IN AND !$config['user_registration']) { ?>
    <div class="alert alert-warning">
        <?php echo $lang['public_compare_user_registration_off']; ?>
    </div>
    <?php } ?>
    <?php if($discount_codes) { ?>
        <div class="alert alert-success">
        <h4><?php echo $lang['public_compare_available_discounts']; ?></h4>
        <?php foreach($discount_codes AS $discount_code) { ?>
            <b><?php echo $discount_code['title']; ?></b>
            <p>
                <?php echo $discount_code['description']; ?>
            </p>
        <?php } ?>
        </div>
    <?php } ?>
    <?php $column_width = floor(12/(count($membership_names))); ?>
    <div class="row">
    <?php foreach($products AS $product) { ?>
    <div class="col-lg-<?php echo $column_width; ?> col-md-<?php echo $column_width; ?> col-sm-<?php echo $column_width; ?>">
    <div class="panel panel-default">
        <div class="panel-heading"><strong><?php echo $product['title']; ?></strong></div>
        <?php if(!empty($product['description'])) { ?>
            <div class="panel-body">
                <?php echo $product['description']; ?>
            </div>
        <?php } ?>
        <ul class="list-group">
            <?php foreach($options AS $option_name=>$option) { ?>
                <li class="list-group-item"><?php echo $option; ?>
                    <span class="pull-right">
                    <?php if($product['options'][$option_name] === 'yes') { ?>
                        <i class="glyphicon glyphicon-ok text-success"></i>
                    <?php } elseif($product['options'][$option_name] == '-') { ?>
                        <i class="fa fa-times text-danger"></i>
                    <?php } else { ?>
                        <span class="badge"><?php echo $this->escape($product['options'][$option_name]); ?></span>
                    <?php } ?>
                    </span>
                </li>
            <?php } ?>
            <li class="list-group-item">
            <?php foreach($product['pricing'] as $key=>$value) { ?>
                    <p>
                    <?php if(count($product['pricing']) > 1) { ?><strong><?php echo $lang['public_compare_option']; ?> <?php echo $key+1; ?></strong><br /><?php } ?>
                    <?php if($value['label']) { ?>
                        <?php echo $this->escape($value['label']); ?><br />
                    <?php } else { ?>
                        <?php echo $lang['public_compare_term']; ?>:
                        <?php if($value['period_count']) { ?>
                             <?php echo $this->escape($value['period_count']); ?> <?php echo $this->escape($value['period']); ?><br />
                        <?php } else { ?>
                            <?php echo $lang['public_compare_lifetime']; ?><br />
                        <?php } ?>
                        <?php if($value['setup_price'] != '0.00') { ?>
                            <?php echo $lang['public_compare_setup']; ?>: <?php echo $this->escape(format_number_currency($value['setup_price'])); ?><br />
                        <?php } ?>
                        <?php echo $lang['public_compare_price']; ?>:
                        <?php if($value['price'] != '0.00') { ?>
                            <?php echo $this->escape(format_number_currency($value['price'])); ?><br />
                        <?php } else { ?>
                            <?php echo $lang['public_compare_free']; ?><br />
                        <?php } ?>
                    <?php } ?>
                    </p>
                    <?php if(LOGGED_IN OR $config['user_registration']) { ?>
                        <a class="btn btn-sm btn-success" href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_orders_add_listing.php?pricing_id=<?php echo $value['id']; ?>"><?php echo $lang['public_compare_order']; ?></a><br /><br />
                    <?php } ?>
            <?php } ?>
            </li>
        </ul>
    </div>
    </div>
    <?php } ?>
    </div>
<?php } ?>