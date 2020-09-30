<?php
class RSS_Block extends Template_Block {
    function content($parameters = array()) {
        if(isset($parameters['template'])) {
            $template = $parameters['template'];
        } else {
            $template = 'rss_template_block';
        }
        $rss_template = $this->PMDR->getNew('Template',dirname(__FILE__).'/'.$template.'.tpl');
        $content = $this->parseFeed($rss_template);
        return $content;
    }

    function parseFeed($rss_template) {
        $content = $rss_template->render();
        if(preg_match_all('/{external_feed_(\d+)}/',$content,$matches)) {
            $feeds = $this->PMDR->get('TableGateway',T_FEEDS_EXTERNAL);
            $rss = $this->PMDR->get('RSS_Parser');

            foreach($matches[1] AS $match) {
                $feed = $feeds->getRow(array('id'=>$match));
                $rss->parse($feed['url']);
                $replace_with = '';
                if($rss->hasData()) {
                    $rss_items = $rss->getItems();
                    $rss_items = array_slice($rss_items,0,5);
                    $feed_content = $this->PMDR->getNew('Template',dirname(__FILE__).'/rss_feed.tpl');
                    $feed_content->set('items',$rss_items);
                    $replace_with = $feed_content->render();
                }
                $content = str_replace('{external_feed_'.$match.'}',$replace_with,$content);
            }
        }
        return $content;
    }
}
?>