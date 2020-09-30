<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_reviews($j) {
    global $PMDR, $db;

    // Update comment counts
    $db->Execute("UPDATE ".T_REVIEWS." r SET r.comment_count = (SELECT COUNT(*) FROM ".T_REVIEWS_COMMENTS." rc WHERE rc.review_id=r.id AND rc.status='active')");

    // Update helpfulness
    $db->Execute("UPDATE ".T_REVIEWS." r SET r.helpful_count = (SELECT COUNT(*) FROM ".T_REVIEWS_QUALITY." WHERE review_id=r.id AND helpful=1)");
    $db->Execute("UPDATE ".T_REVIEWS." r SET r.helpful_total = (SELECT COUNT(*) FROM ".T_REVIEWS_QUALITY." WHERE review_id=r.id)");

    return array('status'=>true);
}
$cron['cron_reviews'] = array('day'=>-1,'hour'=>0,'minute'=>0,'run_order'=>6);
?>