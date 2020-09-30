<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_sitemaps($j) {
    global $PMDR, $db;

    $sitemap = $PMDR->get('SitemapIndex');

    // Initialize the CRON data to an empty array
    $cron_data['data']['sitemaps_submitted'] = array();

    // Try to ping Google.com and record results if successful
    if($sitemap->pingSearchEngine('http://www.google.com/webmasters/tools/ping?sitemap=',BASE_URL.'/xml.php?type=sitemap')) {
        $cron_data['data']['sitemaps_submitted'][] = 'google';
        $cron_data['status'] = true;
    }

    // Try to ping Bing.com and record results if successful
    if($sitemap->pingSearchEngine('http://www.bing.com/webmaster/ping.aspx?siteMap=',BASE_URL.'/xml.php?type=sitemap')) {
        $cron_data['data']['sitemaps_submitted'][] = 'bing';
        $cron_data['status'] = true;
    }

    // Return the CRON data used in the CRON log and CRON report email
    return $cron_data;
}
// Add the CRON job to the queue and set it to run every day
$cron['cron_sitemaps'] = array('day'=>-1,'hour'=>0,'minute'=>0,'run_order'=>8);
?>