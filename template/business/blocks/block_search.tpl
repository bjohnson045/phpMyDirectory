<form action="<?php echo BASE_URL; ?>/search_results.php" method="get" class="form-inline" role="search">
    <div class="form-group">
        <?php echo $form->getFieldHTML('keyword',array('placeholder'=>$lang['public_general_search_find'])); ?>
    </div>
    <!--
    <div class="form-group hidden-md hidden-sm">
        <?php echo $form->getFieldHTML('category'); ?>
    </div>
    -->
    <div class="form-group">
        <?php echo $form->getFieldHTML('location',array('placeholder'=>$lang['public_general_search_location'])); ?>
    </div>
    <!--
    <div class="form-group">
        <?php echo $form->getFieldHTML('location_id'); ?>
    </div>
    -->
    <?php if($form->fieldExists('zip_miles')) { ?>
        <div class="form-group">
        <?php echo $form->getFieldHTML('zip_miles'); ?>
        </div>
    <?php } ?>
    <?php echo $form->getFieldHTML('submit_search',array('class'=>'btn-primary')); ?>
    <a href="<?php echo BASE_URL; ?>/search.php" class="btn btn-default btn-muted"><i class="fa fa-search-plus"></i></a>
</form>
