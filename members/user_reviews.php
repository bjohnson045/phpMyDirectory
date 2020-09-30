<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_reviews'));

$PMDR->get('Authentication')->authenticate();

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_reviews.tpl');

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_reviews'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_reviews.php','text'=>$PMDR->getLanguage('user_reviews')));

$users = $PMDR->get('Users');

$user = $users->getRow($PMDR->get('Session')->get('user_id'));

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList',array('template'=>PMDROOT.TEMPLATE_PATH.'members/blocks/user_reviews_list.tpl'));
    $table_list->addColumn('title',$PMDR->getLanguage('user_reviews_title'));
    $table_list->addColumn('rating',$PMDR->getLanguage('user_reviews_rating'));
    $table_list->addColumn('date',$PMDR->getLanguage('user_reviews_date'));
    $table_list->addColumn('status',$PMDR->getLanguage('user_reviews_status'));
    $table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));

    $records = $db->GetAll("
    SELECT reviews.*, ra.rating, COUNT(rc.id) AS comments
    FROM
        (SELECT r.* FROM ".T_REVIEWS." r WHERE r.user_id=? ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'date DESC')." LIMIT ?,?) AS reviews
        LEFT JOIN ".T_RATINGS." ra ON reviews.rating_id=ra.id
        LEFT JOIN ".T_REVIEWS_COMMENTS." rc ON reviews.id=rc.review_id
    GROUP BY reviews.id
    ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'date DESC'),
    array($user['id'],$table_list->paging->limit1,$table_list->paging->limit2));

    $table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_REVIEWS." r WHERE user_id=?",array($user['id'])));

    foreach($records as $key=>$record) {
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDate($record['date']);
        $records[$key]['rating_static'] = $PMDR->get('Ratings')->printRatingStatic($record['rating']);
    }
    $table_list->addRecords($records);
    $template_content->set('content',$table_list->render());
} else {
    $template_content_form = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_reviews_form.tpl');

    // Set up the form for adding/editing classifieds
    $form = $PMDR->get('Form');
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('user_reviews_title')));
    $form->addField('review','textarea',array('label'=>$PMDR->getLanguage('user_reviews_review')));
    $form->addField('rating','stars',array('label'=>'Overall Rating','fieldset'=>'information','value'=>0));
    $ratings_categories = $db->GetAll("SELECT id, title FROM ".T_RATINGS_CATEGORIES." ORDER BY ordering, title");
    foreach($ratings_categories AS $category) {
        $form->addField('category_'.$category['id'],'stars',array('label'=>$category['title'].' '.$PMDR->getLanguage('user_reviews_rating'),'value'=>0));
    }
    $fields = $PMDR->get('Fields')->addToForm($form,'reviews');
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit')));

    // Validate the fields in the form when submitted
    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('review',new Validate_NonEmpty());
    $form->addValidator('rating',new Validate_NonEmpty());

    $PMDR->setAdd('page_title',$PMDR->getLanguage('user_reviews_edit'));
    // Load the classified content and add to the form
    $edit_review = $PMDR->get('Reviews')->getRow($_GET['id']);
    $form->loadValues($edit_review);

    // If the add/edit form was submitted
    if($form->wasSubmitted('submit')) {
        // Load the form values
        $data = $form->loadValues();

        // Check to see if the form validates
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $data['rating_id'] = $edit_review['rating_id'];
            $PMDR->get('Reviews')->update($data, $_GET['id']);
            $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('user_reviews'))),'update');
            redirect();
        }
    }
    // Render the form to HTML and send to the template
    $template_content_form->set('form',$form);
    $template_content_form->set('fields',$fields);
    $template_content->set('content',$template_content_form);
}

include(PMDROOT.'/includes/template_setup.php');
?>