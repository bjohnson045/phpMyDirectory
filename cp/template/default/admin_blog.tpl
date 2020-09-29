<?php if($users_summary_header) { ?>
    <?php echo $users_summary_header; ?>
    <?php if($listing_header) { ?>
        <?php echo $listing_header; ?>
        <h3><?php echo $lang['admin_blog_posts']; ?></h3>
    <?php } else { ?>
        <h2><?php echo $lang['admin_blog_posts']; ?></h2>
    <?php } ?>
<?php } else { ?>
    <h1><?php echo $lang['admin_blog_posts']; ?></h1>
<?php } ?>
<?php if($form_search) { ?>
    <script type="text/javascript">
    $(document).ready(function() {
        <?php if($_GET['action'] == 'search') { ?>
            $("#blog_search_container").slideToggle();
        <?php } ?>
        $("#blog_search_container .close").click(function() {
            $("#blog_search_container").slideToggle();
            return false;
        });
        $("#blog_search_link").click(function() {
            $("#blog_search_container").slideToggle();
            return false;
        });
    });
    </script>
    <div id="blog_search_container" class="panel panel-default" style="display: none">
        <div class="panel-heading"><?php echo $lang['admin_blog_search']; ?><button type="button" class="close">×</button></div>
        <div class="panel-body">
            <?php echo $form_search->getFormOpenHTML(array('class'=>'form-horizontal')); ?>
            <div class="row">
                <div class="col-md-9">
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('keywords'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('keywords'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('category'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('category'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-offset-4 col-lg-10">
                            <?php echo $form_search->getFieldHTML('submit'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('status'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('status'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('published'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('published'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo $form_search->getFormCloseHTML(); ?>
        </div>
    </div>
<?php } ?>
<?php echo $content; ?>