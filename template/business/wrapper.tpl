<?php echo $header; ?>
    <div class="row">
        <div class="col-xl-9 col-lg-9 col-md-8 col-sm-12 col-xs-12">
            <?php echo $page_header; ?>
            <?php echo $message; ?>
            <?php echo $template_content; ?>
            <p class="text-center"><?php echo $banners->getBanner(2); ?></p>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12 hidden-xs">
            <?php echo $this->block('social_links'); ?>
            <?php echo $this->block('listings_reviews_new'); ?>
            <?php echo $this->block('listings_new'); ?>
            <p class="text-center"><?php echo $banners->getBanner(1); ?></p>
            <?php echo $this->block('listings_popular'); ?>
            <?php echo $this->block('classifieds_featured'); ?>
            <?php echo $this->block('blog_posts'); ?>
            <?php echo $this->block('images_new'); ?>
            <?php echo $this->block('documents_new'); ?>
            <?php echo $this->block('classifieds_new'); ?>
            <?php echo $this->block('events_new'); ?>
            <?php echo $this->block('events_upcoming'); ?>
        </div>
    </div>
<?php echo $footer; ?>