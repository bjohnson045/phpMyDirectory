<ul class="breadcrumb hidden-xs">
    <li><i class="glyphicon glyphicon-home"></i> <a href="<?php echo BASE_URL_NOSSL; ?>"><?php echo $lang['home']; ?></a></li>
    <?php if($breadcrumb) { ?>
        <?php foreach((array) $breadcrumb as $crumb) { ?>
            <li>
            <?php if($crumb['link']) { ?>
                <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a itemprop="url" href="<?php echo $crumb['link']; ?>"><span itemprop="title"><?php echo $this->escape($crumb['text']); ?></span></a></span>
            <?php } else { ?>
                <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title"><?php echo $this->escape($crumb['text']); ?></span></span>
            <?php } ?>
            </li>
        <?php } ?>
    <?php } ?>
</ul>