<?php
include('../../../defaults.php');

echo '<html><body>';

$file_name = basename($_FILES['upload']['name']);
$dir = HTMLEDITOR_PATH;

echo '<script type="text/javascript">';

$file_type = get_uploaded_file_format($_FILES['upload']);

if(DEMO_MODE) {
    echo 'window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "","Disabled in demo.");';
} elseif(!$PMDR->get('Authentication')->checkPermission('admin_login')) {
    echo 'window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "","Permission denied.");';
} elseif(!is_writable(HTMLEDITOR_PATH)) {
    echo 'window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "","Folder permissions error.");';
} elseif(!in_array($file_type,array('image/jpeg','image/jpg','image/gif','image/png','video/x-flv','application/x-shockwave-flash'))) {
    echo 'window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "","Invalid file type.  Must be jpg, jpeg, gif or png.");';
} elseif(move_uploaded_file($_FILES['upload']['tmp_name'], $dir.$file_name)) {
    echo 'window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "'.get_file_url($dir.$file_name).'","File Uploaded!");';
} else {
    echo 'window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "","Error, please try again!");';
}

echo '</script>';

echo '</body></html>';
?>