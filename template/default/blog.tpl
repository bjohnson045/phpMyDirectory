<div class="row">
    <div class="col-lg-8">
        <?php echo $form->getFormOpenHTML(array('class'=>'form-inline')); ?>
        <div class="form-group">
            <?php echo $form->getFieldHTML('keywords'); ?>
        </div>
        <?php echo $form->getFieldHTML('submit'); ?>
        <?php echo $form->getFormCloseHTML(); ?>
    </div>
</div>
<?php if($category) { ?>
    <h2><?php echo $category; ?> <?php echo $lang['public_blog_category_archive']; ?></h2>
    &laquo; <a href="<?php echo $blog_url; ?>" title="<?php echo $lang['public_blog']; ?>"><?php echo $lang['public_blog_back']; ?></a>
<?php } ?>
<?php if(isset($records)) { ?>
    <p style="margin-top: 10px;"><?php echo $lang['public_blog_no_results']; ?></p>
<?php } else { ?>
    <?php echo $content; ?>
    <?php echo $page_navigation; ?>
<?php } ?>
