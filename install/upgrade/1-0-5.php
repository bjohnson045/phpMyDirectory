<?php
function postsync_1_0_5() {
    global $db;
    if($db->GetOne("SELECT value FROM ".T_SETTINGS." WHERE varname='allowed_html_attributes'") == '') {
        $db->Execute("UPDATE ".T_SETTINGS." SET value='width,height,size,color,align,border,class,id,style,title,href,target,alt,face' WHERE varname='allowed_html_attributes'");
    }

    if($db->GetOne("SELECT value FROM ".T_SETTINGS." WHERE varname='admin_allowed_html_tags'") == '') {
        $db->Execute("UPDATE ".T_SETTINGS." SET value='br,font,li,ul,hr,span,p,div,b,i,u,a,strong,em,center,h1,h2,h3' WHERE varname='admin_allowed_html_tags'");
    }

    $db->Execute("UPDATE ".T_USERS_GROUPS." SET name='Awaiting Email Confirmation' WHERE name='Users Awaiting Email Confirmation'");
}
?>