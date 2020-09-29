<?php
include('../../../defaults.php');
echo '<html><body>';

if(isset($_GET['listing_id'])) {
    $listing = $db->GetRow("SELECT id, user_id, images_limit FROM ".T_LISTINGS." WHERE id=?",array($_GET['listing_id']));
    if($listing['user_id'] != $PMDR->get('Session')->get('user_id')) {
        echo 'No files available.';
    } else {
        $images = $db->GetAll("SELECT id, listing_id, title, extension FROM ".T_IMAGES." WHERE listing_id=?",array($listing['id']));
        if(count($images)) {
            echo 'Files:<br />';
            foreach($images AS $image) {
                if(file_exists(IMAGES_PATH.$image['id'].'.'.$image['extension'])) {
                    echo '<a onclick="window.opener.CKEDITOR.tools.callFunction('.intval($_GET['CKEditorFuncNum']).',\''.get_file_url(IMAGES_PATH.$image['id'].'.'.$image['extension']).'\'); window.close();" href="#">'.$image['title'].'</a><br />';
                }
            }
        } else {
            if($listing['images_limit']) {
                echo 'No files available.  You may add images using the <a target="_blank" href="'.BASE_URL.MEMBERS_FOLDER.'user_images.php?action=add&listing_id='.$listing['id'].'">image gallery</a>.';
            } else {

            }
        }
    }
} else {
    $dir = HTMLEDITOR_PATH;
    $files = array();

    if(is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while(($file = readdir($dh)) !== false) {
                if($file != '.' AND $file != '..' AND in_array(get_file_extension($file),array('jpeg','jpg','png','gif'))) {
                    $files[] = $file;
                }
            }
            closedir($dh);
        }
    }
    if(LOGGED_IN AND count($files)) {
        echo 'Files:<br />';
        foreach($files AS $file) {
            echo '<a onclick="window.opener.CKEDITOR.tools.callFunction('.intval($_GET['CKEditorFuncNum']).',\''.get_file_url($dir.$file).'\'); window.close();" href="#">'.$file.'</a><br />';
        }
    } else {
        echo 'No files available.  Please use the upload feature if available.';
    }
}

echo '</body></html>';
?>