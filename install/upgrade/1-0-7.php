<?php
function presync_1_0_7() {}

function postsync_1_0_7() {
    global $db;
    $db->Execute("UPDATE ".T_SETTINGS." SET value='width,height,size,color,align,border,class,id,style,title,href,target,alt,face' WHERE varname = 'admin_allowed_html_attributes' AND value = ''");
    if($db->GetOne("SELECT value FROM ".T_SETTINGS." WHERE varname='allowed_html_tags'") == '') {
        $db->Execute("UPDATE ".T_SETTINGS." SET value='' WHERE varname='allowed_html_attributes'");
    }
}
?>