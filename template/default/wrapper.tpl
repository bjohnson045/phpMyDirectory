<?php echo $header; ?>
    <div class="row row-offcanvas row-offcanvas-left">
        <div class="col-xl-2 col-lg-3 col-md-3 col-sm-4 sidebar-offcanvas" id="sidebar">
            <?php echo $this->block('menu'); ?>
            <div class="hidden-xs">
                <?php if(!LOGGED_IN) { echo $this->block('login'); } ?>
                <?php echo $this->block('categories'); ?>
                <p class="text-center"><?php echo $banners->getBanner(2); ?></p>
                <?php echo $this->block('categories_popular'); ?>
                <?php echo $this->block('listings_new'); ?>
            </div>
        </div>
        <div class="col-xl-8 col-lg-6 col-md-9 col-sm-8 col-xs-12">
            <p class="text-center"><?php echo $banners->getBanner(2); ?></p>
            <?php echo $page_header; ?>
            <?php echo $message; ?>
            <?php echo $template_content; ?>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-9 col-sm-8 col-xs-12">
            <?php echo $this->block('listings_featured'); ?>
            <div class="hidden-xs">
                <?php echo $this->block('listings_reviews_new'); ?>
                <p class="text-center"><?php echo $banners->getBanner(1); ?></p>
                <?php echo $this->block('listings_popular'); ?>
                <?php echo $this->block('classifieds_featured'); ?>
                <?php echo $this->block('blog_categories'); ?>
            <?php echo $this->block('blog_posts'); ?>
            <?php echo $this->block('images_new'); ?>
            <?php echo $this->block('documents_new'); ?>
            <?php echo $this->block('classifieds_new'); ?>
            <?php echo $this->block('events_new'); ?>
            <?php echo $this->block('events_upcoming'); ?>
            </div>
        </div>
    </div>
<?php echo $footer; ?>