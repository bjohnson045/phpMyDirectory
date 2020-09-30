<form action="<?php echo BASE_URL; ?>/search_results.php" method="get" class="navbar-form navbar-left" role="search">
    <div class="form-group">
        <?php echo $form->getFieldHTML('keyword',array('id'=>'keyword_collapsed','placeholder'=>$lang['public_general_search_find'])); ?>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldHTML('category',array('id'=>'category_collapsed')); ?>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldHTML('location',array('id'=>'location_collapsed','placeholder'=>$lang['public_general_search_location'])); ?>
    </div>
    <!--
    <div class="form-group">
        <?php echo $form->getFieldHTML('location_id',array('id'=>'location_id_collapsed')); ?>
    </div>
    -->
    <?php if($form->fieldExists('zip_miles')) { ?>
        <div class="form-group">
        <?php echo $form->getFieldHTML('zip_miles',array('id'=>'zip_miles_collapsed')); ?>
        </div>
    <?php } ?>
    <div class="form-group">
        <?php echo $form->getFieldHTML('submit_search',array('id'=>'submit_search_collapsed','class'=>'btn-primary')); ?>
    </div>
</form>