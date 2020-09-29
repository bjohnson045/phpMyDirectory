<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_email','admin_email_lists','admin_email_campaigns','admin_email_queue','admin_email_log'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_email_manager');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

$template_content->set('title',$PMDR->getLanguage('admin_email_campaigns_send'));

$campaign = $db->GetRow("SELECT * FROM ".T_EMAIL_CAMPAIGNS." WHERE id=?",array($_GET['id']));

if(!isset($_GET['action'])) {
    $form = $PMDR->get('Form');
    $form->addFieldSet('email_campaign',array('legend'=>$PMDR->getLanguage('admin_email_campaigns_campaign')));

    $email_lists = $db->GetAll("SELECT el.*, COUNT(ell.user_id) AS count FROM ".T_EMAIL_LISTS." el LEFT JOIN ".T_EMAIL_LISTS_LOOKUP." ell ON el.id=ell.list_id GROUP BY el.id");
    $send_options = array(
        'all'=>$PMDR->getLanguage('admin_email_campaigns_all')
    );
    if($email_lists) {
        $send_options['email_lists'] = $PMDR->getLanguage('admin_email_campaigns_select_lists');
    }
    $form->addField('send_to','radio',array('label'=>'Send to','options'=>$send_options));

    $options = array();
    foreach($email_lists AS $email_list) {
        $options[$email_list['id']] = $email_list['title'].' ('.$email_list['count'].')';
    }
    if($email_lists) {
        $form->addField('email_lists','checkbox',array('label'=>$PMDR->getLanguage('admin_email_lists'),'options'=>$options,'wrapper_attributes'=>array('style'=>'display: none')));
    } else {
        $form->addFieldNote('send_to','Add <a href="admin_email_lists.php?action=add">Email Lists</a> to send an email to a group of users.');
        $form->setFieldAttribute('send_to','value','all');
    }
    $PMDR->loadJavascript('
    <script type="text/javascript">
    $(document).ready(function(){
        if($("input[name=\'send_to\']:checked").val() == "email_lists") {
            $("#email_lists-control-group").show();
        }
        $("input[name=\'send_to\']").change(function() {
            if($("input[name=\'send_to\']:checked").val() == "all") {
                $("#email_lists-control-group").hide();
            } else {
                $("#email_lists-control-group").show();
            }
        });
    });
    </script>',50);

    $form->addFieldSet('filter',array('legend'=>'Filter'));

    if($campaign['type'] == 'listings') {
        $category_count = $PMDR->get('Categories')->getCount();
        $location_count = $PMDR->get('Locations')->getCount();
        if($category_count > 1) {
            if($PMDR->getConfig('category_select_type') == 'tree_select' OR $PMDR->getConfig('category_select_type') == 'tree_select_multiple') {
                $form->addField('categories',$PMDR->getConfig('category_select_type'),array('label'=>$PMDR->getLanguage('admin_email_campaigns_categories'),'fieldset'=>'filter','first_option'=>'','options'=>$PMDR->get('Categories')->getSelect(),'help'=>$PMDR->getLanguage('admin_mail_help_categories')));
            } else {
                $form->addField('categories','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('admin_email_campaigns_categories'),'fieldset'=>'filter','value'=>'','options'=>array('type'=>'category_tree'),'help'=>$PMDR->getLanguage('admin_mail_help_categories')));
            }
        }
        if($location_count > 1) {
            if($PMDR->getConfig('location_select_type') == 'tree_select' OR $PMDR->getConfig('location_select_type') == 'tree_select_group') {
                $form->addField('locations',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('admin_email_campaigns_locations'),'fieldset'=>'filter','first_option'=>'','options'=>$db->GetAssoc("SELECT id, title, level, left_, right_ FROM ".T_LOCATIONS." WHERE ID != 1 ORDER BY left_"),'help'=>$PMDR->getLanguage('admin_mail_help_locations')));
            } else {
                $form->addField('locations','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('admin_email_campaigns_locations'),'fieldset'=>'filter','value'=>'','options'=>array('type'=>'location_tree'),'help'=>$PMDR->getLanguage('admin_mail_help_locations')));
            }
        }

        $form->addField('pricing_id','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('admin_email_campaigns_products'),'fieldset'=>'filter','options'=>array('type'=>'products_tree','product_type'=>'listing_membership','hidden'=>true),'help'=>$PMDR->getLanguage('admin_mail_help_pricing_id')));
        $form->addField('status','checkbox',array('label'=>$PMDR->getLanguage('admin_email_campaigns_status'),'fieldset'=>'filter','value'=>'','options'=>array('active'=>'Active','pending'=>'Pending','suspended'=>'Suspended'),'help'=>$PMDR->getLanguage('admin_mail_help_status')));
    } elseif($campaign['type'] == 'users') {
        $form->addField('no_order','checkbox',array('label'=>$PMDR->getLanguage('admin_email_campaigns_no_order'),'fieldset'=>'filter','help'=>$PMDR->getLanguage('admin_email_campaigns_no_order_help')));
        $form->addField('unconfirmed_email','checkbox',array('label'=>$PMDR->getLanguage('admin_email_campaigns_unconfirmed_email'),'fieldset'=>'filter','help'=>$PMDR->getLanguage('admin_email_campaigns_unconfirmed_email_help')));
        $form->addField('pricing_id','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('admin_email_campaigns_products'),'fieldset'=>'filter','options'=>array('type'=>'products_tree','product_type'=>'listing_membership','hidden'=>true),'help'=>$PMDR->getLanguage('admin_email_campaigns_send_users_products_help')));
        $form->addDependency('pricing_id',array('type'=>'display','field'=>'no_order','value'=>0));
    }

    $form->addValidator('send_to',new Validate_NonEmpty());
    $form->addField('submit','submit');

    if($form->wasSubmitted('submit')) {
        if(DEMO_MODE) {
            $PMDR->addMessage('error','The mailer is disabled in the demo.');
        } else {
            $data = $form->loadValues();
            if($data['send_to'] == 'email_lists' AND (count($data['email_lists']) == 0 OR !$db->GetOne("SELECT COUNT(*) FROM ".T_EMAIL_LISTS_LOOKUP." WHERE list_id IN(".implode(',',$data['email_lists']).")"))) {
                $form->addError($PMDR->getLanguage('admin_email_campaigns_lists_empty_error'),'email_lists');
            }
            if(!$form->validate()) {
                $PMDR->addMessage('error',$form->parseErrorsForTemplate());
            } else {
                if($campaign['type'] == 'listings') {
                    $query_where = array();
                    if(!is_array($data['categories'])) {
                        $data['categories'] = array_filter(array($data['categories']));
                    }
                    if(!is_array($data['locations'])) {
                        $data['locations'] = array_filter(array($data['locations']));
                    }
                    if(count($data['categories'])) {
                        $query_where[] = 'lc.cat_id IN ('.implode(',',(array) $data['categories']).')';
                        $cat_sql = " INNER JOIN ".T_LISTINGS_CATEGORIES." lc ON l.id=lc.list_id";
                    }
                    if(count($data['locations'])) {
                        $query_where[] = 'l.location_id IN ('.implode(',',(array) $data['locations']).')';
                    }
                    if(count($data['status'])) {
                        $query_where[] = "l.status IN ('".implode("','",(array) $data['status'])."')";
                    }
                    if (count($data['pricing_id']))  {
                        $order_sql = "INNER JOIN ".T_ORDERS." o ON l.id=o.type_id AND o.type='listing_membership'";
                        $query_where[] = "o.pricing_id IN (".implode(',',(array) $data['pricing_id']).")";
                    }
                    if(!empty($data['date_from'])) {
                        $query_where[] = "l.date >= '".$data['date_from']."'";
                    }
                    if(!empty($data['date_to'])) {
                        $query_where[] = "l.date <= '".$data['date_to']."'";
                    }
                    if($data['send_to'] != 'all') {
                        $query_where[] = "l.user_id IN(SELECT user_id FROM ".T_EMAIL_LISTS_LOOKUP." ell WHERE list_id IN(".implode(',',$data['email_lists']).") GROUP BY ell.user_id)";
                    }
                    $where_sql = '';
                    if(count($query_where)) {
                        $where_sql = "WHERE ";
                    }
                    $db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (campaign_id,user_id,type,type_id,date_queued) SELECT ?, l.user_id, 'listing', l.id, NOW() FROM ".T_LISTINGS." l $order_sql $cat_sql $where_sql ".implode(' AND ',$query_where),array($_GET['id']));
                } else {
                    if($data['send_to'] != 'all') {
                        $query_where[] = 'list_id IN('.implode(',',$data['email_lists']).')';
                    }
                    if($data['no_order']) {
                        if($data['send_to'] == 'all') {
                            $order_sql = "LEFT JOIN ".T_ORDERS." o ON u.id=o.user_id";
                        } else {
                            $order_sql = "LEFT JOIN ".T_ORDERS." o ON ell.user_id=o.user_id";
                        }
                        $query_where[] = 'o.user_id IS NULL';
                    }
                    if(count($data['pricing_id']))  {
                        $order_sql = "INNER JOIN ".T_ORDERS." o ON u.id=o.user_id";
                        $query_where[] = "o.pricing_id IN (".implode(',',(array) $data['pricing_id']).") AND o.type='listing_membership' AND o.status='active'";
                    }
                    if($data['unconfirmed_email']) {
                        if($data['send_to'] == 'all') {
                            $order_sql = "LEFT JOIN ".T_USERS_GROUPS_LOOKUP." g ON u.id=g.user_id";
                        } else {
                            $order_sql = "LEFT JOIN ".T_USERS_GROUPS_LOOKUP." g ON ell.user_id=g.user_id";
                        }
                        $query_where[] = 'g.group_id=5';
                    }
                    $where_sql = '';
                    if(count($query_where)) {
                        $where_sql = "WHERE ".implode(' AND ',$query_where);
                    }
                    if($data['send_to'] == 'all') {
                        $db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (campaign_id,user_id,type,type_id,date_queued) SELECT ?, u.id, 'user', u.id, NOW() FROM ".T_USERS." u $order_sql $where_sql",array($_GET['id']));
                    } else {
                        $db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (campaign_id,user_id,type,type_id,date_queued) SELECT ?, ell.user_id, 'user', ell.user_id, NOW() FROM ".T_EMAIL_LISTS_LOOKUP." ell $order_sql $where_sql GROUP BY ell.user_id",array($_GET['id']));
                    }
                }
                $db->Execute("UPDATE ".T_EMAIL_CAMPAIGNS." SET date_sent = NOW() WHERE id=?",array($_GET['id']));
                $PMDR->addMessage('success',$PMDR->getLanguage('admin_email_campaigns_sent'));
                redirect_url(BASE_URL_ADMIN.'/admin_email_campaigns.php');
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}
$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>