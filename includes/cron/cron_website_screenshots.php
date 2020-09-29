<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_website_screenshots($j) {
    global $PMDR, $db;

    // Initially set the number of screenshots captured to 0
    $cron_data['data']['website_screenshots'] = 0;

    // If the addon is enabled, CURL is enabled, and a website screenshot service is enabled
    if(function_exists("curl_exec") AND $PMDR->getConfig('website_screenshot_service') != '') {
        // Get the listing websites from listings allowing websites, the website is not empty, the link is not dead, and the last updated date is older than the number of cache day setting
        $websites = $db->GetAll("SELECT id, www FROM ".T_LISTINGS." WHERE www_screenshot_allow = 1 AND www != '' AND www_status!='dead' AND www_screenshot_last_updated < DATE_SUB('".$PMDR->get('Dates')->dateTimeNow()."',INTERVAL ".$PMDR->getConfig('website_screenshot_cache_days')." DAY) ORDER BY www_screenshot_last_updated ASC LIMIT ".$PMDR->getConfig('website_screenshot_cron_amount'));
        // Loop through each website URL and attempt to get a screenshot using CURL
        foreach($websites AS $website) {
            $image = false;
            $website['www'] = trim($website['www']);
            if($PMDR->getConfig('website_screenshot_service') == 'thumbshots') {
                $parameters = array();
                $parameters['url'] = $website['www'];
                $parameters['v'] = '1';
                $parameters['cid'] = trim(urldecode($PMDR->getConfig('website_screenshot_key')));
                $parameters['w'] = '480';
                $parameters['h'] = '360';

                $ch = curl_init();
                if(trim($parameters['cid']) != '') {
                    curl_setopt($ch, CURLOPT_URL, "http://images.thumbshots.com/image.aspx?".http_build_query($parameters));
                } else {
                    curl_setopt($ch, CURLOPT_URL, "http://open.thumbshots.org/image.aspx?".http_build_query($parameters));
                }
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_REFERER, BASE_URL);
                $image = curl_exec($ch);
                curl_close($ch);
            } elseif($PMDR->getConfig('website_screenshot_service') == 'shrinktheweb') {
                $parameters = array();
                $parameters['stwurl'] = $website['www'];
                $parameters['stwembed'] = '1';
                $parameters['stwaccesskeyid'] = trim(urldecode($PMDR->getConfig('website_screenshot_key')));
                $parameters['stwsize'] = 'xlg';

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://images.shrinktheweb.com/xino.php?".http_build_query($parameters));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_REFERER, BASE_URL);
                $image = curl_exec($ch);
                $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if($response_code != 200) {
                    $image = false;
                }
                curl_close($ch);
            } else {
                break;
            }
            // If a valid response from the screenshot provider API was received
            if($image) {
                // If a valid image object could be created from the API response
                if($image = @imagecreatefromstring($image)) {
                    // If the image could be copied to the screenshot path
                    if(@imagejpeg($image,SCREENSHOTS_PATH.$website['id'].'.jpg')) {
                        // Generate the small screenshot image and save it
                        if($PMDR->getConfig('website_screenshot_size_small')) {
                            $options = array('width'=>$PMDR->getConfig('website_screenshot_size_small'));
                        }
                        $PMDR->get('Image_Handler')->process(SCREENSHOTS_PATH.$website['id'].'.jpg',SCREENSHOTS_PATH.$website['id'].'-small.jpg',$options);
                        // Generate the large screenshot image and save it
                        $options = array();
                        if($PMDR->getConfig('website_screenshot_size')) {
                            $options = array('width'=>$PMDR->getConfig('website_screenshot_size'));
                        }
                        $PMDR->get('Image_Handler')->process(SCREENSHOTS_PATH.$website['id'].'.jpg',SCREENSHOTS_PATH.$website['id'].'.jpg',$options);
                        // Update the listing website screenshot last updated date today
                        $db->Execute("UPDATE ".T_LISTINGS." SET www_screenshot_last_updated = '".$PMDR->get('Dates')->dateTimeNow()."' WHERE id=?",array($website['id']));
                        // Increment the website screenshots counter because a screenshot was successfully generated
                        $cron_data['data']['website_screenshots']++;
                    } else {
                        trigger_error('Could not write screenshot to '.SCREENSHOTS_PATH.$website['id'].'.jpg',E_USER_WARNING);
                    }
                } else {
                    trigger_error('Could not create image from string',E_USER_WARNING);
                }
            }
        }
    }

    // Set the CRON status to true, which is used for reporting in the CRON report email
    $cron_data['status'] = true;

    // Return the CRON data used in the CRON log and CRON report email
    return $cron_data;
}
// Add the CRON job to the queue and set it to run every hour
$cron['cron_website_screenshots'] = array('day'=>-1,'hour'=>-1,'minute'=>0,'run_order'=>1);
?>