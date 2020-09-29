<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_xml'));

header("Content-type: text/xml; charset=".CHARSET);
echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n";

switch($_GET['type']) {
    case 'sitemap':
        if(!($xml = $PMDR->get('Cache')->get('sitemap_xml', 86400, 'sitemap'))) {
            $gsi = $PMDR->get('SitemapIndex');
            $categories_count = $db->GetOne("SELECT COUNT(*) FROM ".T_CATEGORIES." WHERE count_total > 0 AND hidden=0 AND level > 0");
            if($categories_count > 0) {
                $gsi->addSitemap(BASE_URL.'/xml.php?type=sitemap_categories',$PMDR->get('Dates')->dateNow());
            }
            $locations_count = $db->GetOne("SELECT COUNT(*) FROM ".T_LOCATIONS." WHERE count_total > 0 AND hidden=0 AND level > 0");
            if($locations_count > 0) {
                $gsi->addSitemap(BASE_URL.'/xml.php?type=sitemap_locations',$PMDR->get('Dates')->dateNow());
            }
            if($categories_count > 0 AND $locations_count > 0) {
                for($x = 0; $x < ($categories_count / floor(10000 / $locations_count)); $x++) {
                    $gsi->addSitemap(BASE_URL.'/xml.php?type=sitemap_categories_locations&batch='.($x+1),$PMDR->get('Dates')->dateNow());
                }
            }
            $listing_count = $db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." WHERE status='active'");
            for($x = 0; $x < ($listing_count / 10000); $x++) {
                $gsi->addSitemap(BASE_URL.'/xml.php?type=sitemap_listings&batch='.($x+1),$PMDR->get('Dates')->dateNow());
            }
            $event_count = $db->GetOne("SELECT COUNT(*) FROM ".T_EVENTS." WHERE status='active'");
            for($x = 0; $x < ($event_count / 10000); $x++) {
                $gsi->addSitemap(BASE_URL.'/xml.php?type=sitemap_events&batch='.($x+1),$PMDR->get('Dates')->dateNow());
            }
            $classified_count = $db->GetOne("SELECT COUNT(*) FROM ".T_CLASSIFIEDS." WHERE expire_date > NOW() OR expire_date IS NULL");
            for($x = 0; $x < ($classified_count / 10000); $x++) {
                $gsi->addSitemap(BASE_URL.'/xml.php?type=sitemap_classifieds&batch='.($x+1),$PMDR->get('Dates')->dateNow());
            }
            if(ADDON_BLOG) {
                $blog_post_count = $db->GetOne("SELECT COUNT(*) FROM ".T_BLOG." WHERE status='active' AND DATE(date_publish) <= CURDATE()");
                if($blog_post_count) {
                    $gsi->addSitemap(BASE_URL.'/xml.php?type=sitemap_blog',$PMDR->get('Dates')->dateNow());
                }
            }
            if($db->GetOne("SELECT COUNT(*) FROM ".T_PAGES." WHERE active=1")) {
                $gsi->addSitemap(BASE_URL.'/xml.php?type=sitemap_pages',$PMDR->get('Dates')->dateNow());
            }
            if($db->GetOne("SELECT COUNT(*) FROM ".T_SITEMAP_XML." WHERE active=1")) {
                $gsi->addSitemap(BASE_URL.'/xml.php?type=sitemap_urls',$PMDR->get('Dates')->dateNow());
            }
            $xml = $gsi->getXML();
            $PMDR->get('Cache')->write('sitemap_xml',$xml);
        }
        echo $xml;
        break;
    case 'sitemap_categories':
        if(!($xml = $PMDR->get('Cache')->get('sitemap_categories_xml', 86400, 'sitemap'))) {
            $gs = $PMDR->get('Sitemap');
            $categories = $db->GetAll("SELECT id, friendly_url_path, level FROM ".T_CATEGORIES." WHERE count_total > 0 AND hidden=0 AND level > 0 ORDER BY left_");
            foreach($categories AS $category) {
                $gs->addURLTag($PMDR->get('Categories')->getURL($category['id'],$category['friendly_url_path']),$PMDR->get('Dates')->dateNow(),'daily',round(1/$category['level'],1));
            }
            $xml = $gs->getXML();
            $PMDR->get('Cache')->write('sitemap_categories_xml',$xml);
        }
        echo $xml;
        break;
    case 'sitemap_locations':
        if(!($xml = $PMDR->get('Cache')->get('sitemap_locations_xml', 86400, 'sitemap'))) {
            $gs = $PMDR->get('Sitemap');
            $locations = $db->GetAll("SELECT id, friendly_url_path, level FROM ".T_LOCATIONS." WHERE count_total > 0 AND hidden=0 AND level > 0 ORDER BY left_");
            foreach($locations AS $location) {
                $gs->addURLTag($PMDR->get('Locations')->getURL($location['id'],$location['friendly_url_path']),$PMDR->get('Dates')->dateNow(),'daily',round(1/($location['level'] ? $location['level'] : 1),1));
            }
            $xml = $gs->getXML();
            $PMDR->get('Cache')->write('sitemap_locations_xml',$xml);
        }
        echo $xml;
        break;
    case 'sitemap_categories_locations':
        if(!($xml = $PMDR->get('Cache')->get('sitemap_categories_locations_xml', 86400, 'sitemap'))) {
            $gs = $PMDR->get('Sitemap');
            $categories_count = $db->GetOne("SELECT COUNT(*) FROM ".T_CATEGORIES." WHERE count_total > 0 AND hidden=0 AND level > 0");
            $locations_count = $db->GetOne("SELECT COUNT(*) FROM ".T_LOCATIONS." WHERE count_total > 0 AND hidden=0 AND level > 0");
            if($locations_count AND $categories_count) {
                $categories = $db->GetAll("SELECT id, friendly_url_path, level FROM ".T_CATEGORIES." WHERE count_total > 0 AND hidden=0 AND level > 0 ORDER BY left_");
                $total_categories = count($categories);
                $locations = $db->GetAll("SELECT id, friendly_url_path, level FROM ".T_LOCATIONS." WHERE count_total > 0 AND hidden=0 AND level > 0 ORDER BY left_");
                $category_count = floor(10000 / $locations_count);
                if($category_count == 0) {
                    $category_count = 1;
                }
                if(!isset($_GET['batch'])) {
                    $_GET['batch'] = 1;
                }
                $category_limit1 = ($_GET['batch'] == 1) ? 0 : ($_GET['batch']-1) * $category_count;
                if($total_categories < ($category_limit1+$category_count)) {
                    $category_limit2 = $total_categories;
                } else {
                    $category_limit2 = $category_limit1+$category_count;
                }
                for($x = $category_limit1; $x < $category_limit2; $x++) {
                    $category = $categories[$x];
                    foreach($locations AS $location) {
                        $gs->addURLTag($PMDR->get('Categories')->getURL($category['id'],$category['friendly_url_path'],$location['id'],$location['friendly_url_path']),$PMDR->get('Dates')->dateNow(),'daily',round(1/(($category['level']+$location['level'])/2),1));
                    }
                }
            }
            $xml = $gs->getXML();
            $PMDR->get('Cache')->write('sitemap_categories_locations_xml',$xml);
        }
        echo $xml;
        break;
    case 'sitemap_listings':
        if(!($xml = $PMDR->get('Cache')->get('sitemap_listings_xml', 86400, 'sitemap'))) {
            $gs = $PMDR->get('Sitemap');
            if(!isset($_GET['batch'])) {
                $_GET['batch'] = 1;
            }
            $limit1 = ($_GET['batch'] <= 1) ? 0 : ($_GET['batch'] -1) * 10000;
            // Order by DESC so newly added records get priority
            $listings = $db->GetAll("SELECT id, friendly_url, IF(date_update IS NOT NULL,date_update,date) AS date, priority FROM ".T_LISTINGS." WHERE status='active' ORDER BY id DESC LIMIT $limit1, 10000");
            foreach($listings AS $f) {
                if(!$f['priority']) $f['priority'] = 1;
                if($f['priority'] > 10) $f['priority'] = 10;
                $gs->addURLTag($PMDR->get('Listings')->getURL($f['id'],$f['friendly_url']),$PMDR->get('Dates')->formatDate($f['date'],'Y-m-d'),'daily',round(substr($f['priority'],0,2)/10,1));
            }
            $xml = $gs->getXML();
            $PMDR->get('Cache')->write('sitemap_listings_xml',$xml);
        }
        echo $xml;
        break;
    case 'sitemap_events':
        if(!($xml = $PMDR->get('Cache')->get('sitemap_events_xml', 86400, 'sitemap'))) {
            $gs = $PMDR->get('Sitemap');
            if(!isset($_GET['batch'])) {
                $_GET['batch'] = 1;
            }
            $limit1 = ($_GET['batch'] == 1) ? 0 : ($_GET['batch'] -1) * 10000;
            // Order by DESC so newly added records get priority
            $events = $db->GetAll("SELECT id, friendly_url, IF(date_update IS NOT NULL,date_update,date) AS date FROM ".T_EVENTS." WHERE status='active' ORDER BY id DESC LIMIT $limit1, 10000");
            foreach($events AS $f) {
                if(!$f['priority']) $f['priority'] = 1;
                if($f['priority'] > 10) $f['priority'] = 10;
                $gs->addURLTag($PMDR->get('Events')->getURL($f['id'],$f['friendly_url']),$PMDR->get('Dates')->formatDate($f['date'],'Y-m-d'),'daily',1);
            }
            $xml = $gs->getXML();
            $PMDR->get('Cache')->write('sitemap_events_xml',$xml);
        }
        echo $xml;
        break;
    case 'sitemap_classifieds':
        if(!($xml = $PMDR->get('Cache')->get('sitemap_classifieds_xml', 86400, 'sitemap'))) {
            $gs = $PMDR->get('Sitemap');
            if(!isset($_GET['batch'])) {
                $_GET['batch'] = 1;
            }
            $limit1 = ($_GET['batch'] == 1) ? 0 : ($_GET['batch']-1) * 10000;
            // Order by DESC so newly added records get priority
            $classifieds = $db->GetAll("SELECT id, friendly_url, date FROM ".T_CLASSIFIEDS." WHERE (expire_date > NOW() OR expire_date IS NULL) ORDER BY id DESC LIMIT $limit1, 10000");
            foreach($classifieds AS $classified) {
                $gs->addURLTag($PMDR->get('Classifieds')->getURL($classified['id'],$classified['friendly_url']),$PMDR->get('Dates')->formatDate($classified['date'],'Y-m-d'),'daily',1);
            }
            $xml = $gs->getXML();
            $PMDR->get('Cache')->write('sitemap_classifieds_xml',$xml);
        }
        echo $xml;
        break;
    case 'sitemap_blog':
        if(!($xml = $PMDR->get('Cache')->get('sitemap_blog_xml', 86400, 'sitemap'))) {
            $gs = $PMDR->get('Sitemap');
            // Order by DESC so newly added records get priority
            $blog_posts = $db->GetAll("SELECT id, friendly_url FROM ".T_BLOG." WHERE status='active' AND DATE(date_publish) <= CURDATE() ORDER BY id DESC");
            foreach($blog_posts AS $post) {
                $gs->addURLTag($PMDR->get('Blog')->getURL($post['id'],$post['friendly_url']),$PMDR->get('Dates')->formatDate($f['publish_date'],'Y-m-d'));
            }
            $xml = $gs->getXML();
            $PMDR->get('Cache')->write('sitemap_blog_xml',$xml);
        }
        echo $xml;
        break;
    case 'sitemap_pages':
        if(!($xml = $PMDR->get('Cache')->get('sitemap_pages_xml', 86400, 'sitemap'))) {
            $gs = $PMDR->get('Sitemap');
            $gs->addURLTag(BASE_URL,$PMDR->get('Dates')->dateNow(),'daily',1);
            if($links = $PMDR->get('CustomLinks')->getLinks(false,true)) {
                foreach($links AS $link) {
                    $priority = 0.5;
                    if(!empty($link['sitemap_xml_priority']) AND $link['sitemap_xml_priority'] >= 0.0 AND $link['sitemap_xml_priority'] <= 1.0) {
                        $priority = $link['sitemap_xml_priority'];
                    }
                    $gs->addURLTag($link['url'],$PMDR->get('Dates')->dateNow(),'daily',$priority);
                }
            }
            $xml = $gs->getXML();
            $PMDR->get('Cache')->write('sitemap_pages_xml',$xml);
        }
        echo $xml;
        break;
    case 'sitemap_urls':
        if(!($xml = $PMDR->get('Cache')->get('sitemap_urls_xml', 86400, 'sitemap'))) {
            $gs = $PMDR->get('Sitemap');
            if($urls = $PMDR->get('Sitemap_XML')->getURLs()) {
                foreach($urls AS $url) {
                    if($PMDR->get('Dates')->isZero($url['date_updated'])) {
                        $url['date_updated'] = null;
                    }
                    $gs->addURLTag($url['url'],$url['date_updated'],$url['frequency'],$url['priority']);
                }
            }
            $xml = $gs->getXML();
            $PMDR->get('Cache')->write('sitemap_urls_xml',$xml);
        }
        echo $xml;
        break;
    case 'rss_recent_listings':
        echo '<?xml-stylesheet type="text/xsl" href="'.$PMDR->get('Templates')->url('rss.xsl').'"?>';
        // Check cache for complete rss cache, if so output it
        if($output = $PMDR->get('Cache')->get('rss_recent_listings', 1800)) {
            echo $output;
        } else {
            $rss = $PMDR->get('RSS');
            $rss->title = $PMDR->getLanguage('public_xml_recent_listings');
            $rss->url = BASE_URL.'/xml.php?type=rss_recent_listings';
            $rss->link = BASE_URL;
            $rss->description = $PMDR->getLanguage('public_xml_recent_listings_description');
            // We check to see if have the recent listings array cached from the CRON JOB
            $recent_listings = $PMDR->get('Cache')->get('recent_listings', 1800);
            // If not cached from CRON, go ahead and do the query (slow!) and write the cache ourselves
            if(!$recent_listings) {
                $recent_listings = $db->GetAll("SELECT id, friendly_url, title, description_short, date FROM ".T_LISTINGS." WHERE status='active' ORDER BY date DESC LIMIT 5");
                $PMDR->get('Cache')->write('recent_listings',$recent_listings);
            }
            // Since we now have our recent listings array, write the RSS
            foreach($recent_listings as $listing) {
                $rss->addItem($listing['title'],$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']),$listing['description_short'],$PMDR->get('Dates')->formatDate($listing['date'],'D, d M Y H:i:s O'));
            }
            $output = $rss->getRSS();
            // We go ahead and write to the rss cache also, which may be unnecesarry but adds a second layer of caching
            $PMDR->get('Cache')->write('rss_recent_listings',$output);
            echo $output;
        }
        break;
    case 'rss_classifieds_featured':
        echo '<?xml-stylesheet type="text/xsl" href="'.$PMDR->get('Templates')->url('rss.xsl').'"?>';
        // Check cache for complete rss cache, if so output it
        if($output = $PMDR->get('Cache')->get('rss_classifieds_featured', 1800)) {
            echo $output;
        } else {
            $rss = $PMDR->get('RSS');
            $rss->title = $PMDR->getLanguage('public_xml_classifieds_featured');
            $rss->url = BASE_URL.'/xml.php?type=rss_classifieds_featured';
            $rss->link = BASE_URL;
            $rss->description = $PMDR->getLanguage('public_xml_classifieds_featured_description');
            // We check to see if have the featured classifieds array cached from the CRON JOB
            $classifieds_featured = $PMDR->get('Cache')->get('classifieds_featured', 1800);
            // If not cached from CRON, go ahead and do the query (slow!) and write the cache ourselves
            if(!$classifieds_featured) {
                $classifieds_featured = $PMDR->get('Classifieds')->getFeatured(10);
                $PMDR->get('Cache')->write('classifieds_featured',$classifieds_featured);
            }
            // Since we now have our recent listings array, write the RSS
            foreach($classifieds_featured as $classified) {
                $rss->addItem($classified['title'],$PMDR->get('Classifieds')->getURL($classified['id'],$classified['friendly_url']),$classified['description'],$PMDR->get('Dates')->formatDate($classified['date'],'D, d M Y H:i:s O'));
            }
            $output = $rss->getRSS();
            // We go ahead and write to the rss cache also, which may be unnecesarry but adds a second layer of caching
            $PMDR->get('Cache')->write('rss_classifieds_featured',$output);
            echo $output;
        }
        break;
    case 'rss_events_new':
        echo '<?xml-stylesheet type="text/xsl" href="'.$PMDR->get('Templates')->url('rss.xsl').'"?>';
        // Check cache for complete rss cache, if so output it
        if($output = $PMDR->get('Cache')->get('rss_events_new', 1800)) {
            echo $output;
        } else {
            $rss = $PMDR->get('RSS');
            $rss->title = $PMDR->getLanguage('public_xml_events_new');
            $rss->url = BASE_URL.'/xml.php?type=rss_events_new';
            $rss->link = BASE_URL;
            $rss->description = $PMDR->getLanguage('public_xml_events_new_description');
            // We check to see if have the recent listings array cached from the CRON JOB
            $events = $PMDR->get('Cache')->get('events_new', 1800);
            // If not cached from CRON, go ahead and do the query (slow!) and write the cache ourselves
            if(!$events) {
                $events = $db->GetAll("SELECT e.id, e.friendly_url, e.title, e.description_short, ed.date_start FROM ".T_EVENTS." e INNER JOIN ".T_EVENTS_DATES." ed ON e.id=ed.event_id WHERE e.status='active' ORDER BY e.date DESC LIMIT 5");
                $PMDR->get('Cache')->write('events_new',$events);
            }
            // Since we now have our recent listings array, write the RSS
            foreach($events as $event) {
                $rss->addItem($event['title'],$PMDR->get('Events')->getURL($event['id'],$event['friendly_url']),$event['description_short'],$PMDR->get('Dates')->formatDate($event['date_start'],'D, d M Y H:i:s O'));
            }
            $output = $rss->getRSS();
            // We go ahead and write to the rss cache also, which may be unnecesarry but adds a second layer of caching
            $PMDR->get('Cache')->write('rss_events_new',$output);
            echo $output;
        }
        break;
    case 'rss_recent_reviews':
        echo '<?xml-stylesheet type="text/xsl" href="'.$PMDR->get('Templates')->url('rss.xsl').'"?>';
        // Check cache for complete rss cache, if so output it
        if($output = $PMDR->get('Cache')->get('rss_recent_reviews', 1800)) {
            echo $output;
        } else {
            $rss = $PMDR->get('RSS');
            $rss->title = $PMDR->getLanguage('public_xml_recent_reviews');
            $rss->url = BASE_URL.'/xml.php?type=rss_recent_reviews';
            $rss->link = BASE_URL;
            $rss->description = $PMDR->getLanguage('public_xml_recent_reviews_description');
            // We check to see if have the recent listings array cached from the CRON JOB
            $recent_reviews = $PMDR->get('Cache')->get('recent_reviews', 1800);
            // If not cached from CRON, go ahead and do the query (slow!) and write the cache ourselves
            if(!$recent_reviews) {
                $recent_reviews = $db->GetAll("SELECT id, title, review, date FROM ".T_REVIEWS." WHERE status='active' ORDER BY date DESC LIMIT 5");
                $PMDR->get('Cache')->write('recent_reviews',$recent_reviews);
            }
            // Since we now have our recent listings array, write the RSS
            foreach($recent_reviews as $review) {
                $rss->addItem($review['title'],BASE_URL.'/listing_reviews.php?review_id='.$review['id'],$review['review'],$PMDR->get('Dates')->formatDate($review['date'],'D, d M Y H:i:s O'));
            }
            $output = $rss->getRSS();
            // We go ahead and write to the rss cache also, which may be unnecesarry but adds a second layer of caching
            $PMDR->get('Cache')->write('rss_recent_reviews',$output);
            echo $output;
        }
        break;
    case 'rss_popular_categories':
        echo '<?xml-stylesheet type="text/xsl" href="'.$PMDR->get('Templates')->url('rss.xsl').'"?>';
        if($output = $PMDR->get('Cache')->get('rss_popular_categories', 1800)) {
            echo $output;
        } else {
            $rss = $PMDR->get('RSS');
            $rss->title = $PMDR->getLanguage('public_xml_popular_categories');
            $rss->url = BASE_URL.'/xml.php?type=rss_popular_categories';
            $rss->link = BASE_URL;
            $rss->description = $PMDR->getLanguage('public_xml_popular_categories_description');
            $popular_categories = $PMDR->get('Categories')->getPopular(5);
            foreach($popular_categories as $category) {
                $rss->addItem($category['title'],$PMDR->get('Categories')->getURL($category['id'],$category['friendly_url_path']),$category['description_short']);
            }
            $output = $rss->getRSS();
            $PMDR->get('Cache')->write('rss_popular_categories',$output);
            echo $output;
        }
        break;
    case 'rss_popular_listings':
        echo '<?xml-stylesheet type="text/xsl" href="'.$PMDR->get('Templates')->url('rss.xsl').'"?>';
        // Check cache for complete rss cache, if so output it
        if($output = $PMDR->get('Cache')->get('rss_popular_listings', 1800)) {
            echo $output;
        } else {
            $rss = $PMDR->get('RSS');
            $rss->title = $PMDR->getLanguage('public_xml_popular_listings');
            $rss->url = BASE_URL.'/xml.php?type=rss_popular_listings';
            $rss->link = BASE_URL;
            $rss->description = $PMDR->getLanguage('public_xml_popular_listings_description');
            // We check to see if have the popular listings array cached from the CRON JOB
            $popular_listings = $PMDR->get('Cache')->get('popular_listings', 1800);
            // If not cached from CRON, go ahead and do the query (slow!) and write the cache ourselves
            if(!$popular_listings) {
                $popular_listings = $db->GetAll("SELECT id, description_short, title, friendly_url, impressions, date, location_id, location_text_1, location_text_2, location_text_3
                   FROM ".T_LISTINGS."
                   WHERE status = 'active'
                   ORDER BY impressions DESC LIMIT 5");
                $PMDR->get('Cache')->write('popular_listings',$popular_listings);
            }
            // Since we now have our popular listings array, write the RSS
            foreach($popular_listings as $listing) {
                $rss->addItem($listing['title'],$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']),$listing['description_short'],$PMDR->get('Dates')->formatDate($listing['date'],'D, d M Y H:i:s O'));
            }
            $output = $rss->getRSS();
            // We go ahead and write to the rss cache also, which may be unnecesarry but adds a second layer of caching
            $PMDR->get('Cache')->write('rss_popular_listings',$output);
            echo $output;
        }
        break;
    case 'rss_featured_listings':
        echo '<?xml-stylesheet type="text/xsl" href="'.$PMDR->get('Templates')->url('rss.xsl').'"?>';
        // Check cache for complete rss cache, if so output it
        if($output = $PMDR->get('Cache')->get('rss_featured_listings', 1800)) {
            echo $output;
        } else {
            $rss = $PMDR->get('RSS');
            $rss->title = $PMDR->getLanguage('public_xml_featured_listings');
            $rss->url = BASE_URL.'/xml.php?type=rss_featured_listings';
            $rss->link = BASE_URL;
            $rss->description = $PMDR->getLanguage('public_xml_featured_listings_description');
            // We check to see if have the popular listings array cached from the CRON JOB
            $featured_listings = $PMDR->get('Cache')->get('featured_listings', 1800);
            // If not cached from CRON, go ahead and do the query (slow!) and write the cache ourselves
            if(!$featured_listings) {
                $featured_listings = $db->GetAll("SELECT description_short, ".T_LISTINGS.".id, title, ".T_LISTINGS.".friendly_url, date
                                    FROM ".T_LISTINGS."
                                    WHERE
                                        status = 'active'
                                        AND featured = 1
                                    GROUP BY ".T_LISTINGS.".id
                                    ORDER by RAND(), ".T_LISTINGS.".priority
                                    LIMIT 5");
                $PMDR->get('Cache')->write('featured_listings',$featured_listings);
            }
            // Since we now have our popular listings array, write the RSS
            foreach($featured_listings as $listing) {
                $rss->addItem($listing['title'],$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']),$listing['description_short'],$PMDR->get('Dates')->formatDate($listing['date'],'D, d M Y H:i:s O'));
            }
            $output = $rss->getRSS();
            // We go ahead and write to the rss cache also, which may be unnecesarry but adds a second layer of caching
            $PMDR->get('Cache')->write('rss_featured_listings',$output);
            echo $output;
        }
        break;
    case 'rss_blog':
        echo '<?xml-stylesheet type="text/xsl" href="'.$PMDR->get('Templates')->url('rss.xsl').'"?>';
        // Check cache for complete rss cache, if so output it
        if($output = $PMDR->get('Cache')->get('rss_blog', 1800)) {
            echo $output;
        } else {
            $rss = $PMDR->get('RSS');
            $rss->title = $PMDR->getLanguage('public_xml_blog');
            $rss->url = BASE_URL.'/xml.php?type=rss_blog';
            $rss->link = BASE_URL;
            $rss->description = $PMDR->getLanguage('public_xml_blog_description');
            // We check to see if have the popular listings array cached from the CRON JOB
            $blog_posts = $PMDR->get('Cache')->get('blog_posts', 1800);
            // If not cached from CRON, go ahead and do the query (slow!) and write the cache ourselves
            if(!$blog_posts) {
                $blog_posts = $db->GetAll("SELECT * FROM ".T_BLOG." WHERE DATE(date_publish) <= CURDATE() AND status='active' ORDER BY date_publish DESC LIMIT 10");
                $PMDR->get('Cache')->write('blog_posts',$blog_posts);
            }
            // Since we now have our popular listings array, write the RSS
            foreach($blog_posts as $blog_post) {
                $rss->addItem($blog_post['title'],$PMDR->get('Blog')->getURL($blog_post['id'],$blog_post['friendly_url']),$blog_post['content_short'],$PMDR->get('Dates')->formatDate($blog_post['date_publish'],'D, d M Y H:i:s O'));
            }
            $output = $rss->getRSS();
            // We go ahead and write to the rss cache also, which may be unnecesarry but adds a second layer of caching
            $PMDR->get('Cache')->write('rss_blog_posts',$output);
            echo $output;
        }
        break;
}
?>