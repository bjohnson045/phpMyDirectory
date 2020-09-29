<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_remote_features'));

$PMDR->get('Authentication')->checkPermission('admin_remote_features');

$output_cat = '
<form class="form-inline" action="'.BASE_URL.'/search_results.php" method="get">
<table border="0" cellpadding="3" cellspacing="0">
 <tr>
  <td>
    <table border="0" width="100%"><tr><td>'.$PMDR->getLanguage('admin_remote_features_search').':
    <input class="form-control" style="width: 150px" type="text" name="keyword" size="20" maxlength="64">&nbsp;';
    $output_cat.= '<select class="form-control" name="category">';
    $r_search = $PMDR->get('Categories')->getRoots();
    $output_cat.= '<option value="">'.$PMDR->getLanguage('admin_remote_features_select_categories').'</option>';
    foreach($r_search as $f_search) {
        $output_cat.= '<option value="'.$f_search['id'].'">'.$PMDR->get('Cleaner')->clean_output($f_search['title']).'</option>';
    }
    $output_cat.= '</select>';

$output_cat .= '&nbsp;<input class="btn btn-default" type="submit" name="submit" value="'.$PMDR->getLanguage('admin_submit').'">
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</form>';

$output_loc = '
<form class="form-inline" action="'.BASE_URL.'/search_results.php" method="get">
<table border="0" cellpadding="3" cellspacing="0">
 <tr>
  <td>
    <table border="0" width="100%"><tr><td>'.$PMDR->getLanguage('admin_remote_features_keyword').'</td><td>'.$PMDR->getLanguage('admin_remote_features_zipcode').'</td><td></td></tr>
    <tr>
        <td><input class="form-control" style="width: 150px" type="text" name="keyword" size="20" maxlength="64"></td>
        <td><input class="form-control" style="width: 150px" type="text" name="zip" size="20" maxlength="64"></td>
        <td><input class="btn btn-default" type="submit" name="submit" value="'.$PMDR->getLanguage('admin_submit').'"></td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</form>';

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'/admin_remote_features.tpl');
$template_content->set('output_cat',$output_cat);
$template_content->set('output_cat_encoded',htmlspecialchars($output_cat));
$template_content->set('output_loc',$output_loc);
$template_content->set('output_loc_encoded',htmlspecialchars($output_loc));

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>