<?php
class Events_Calendar_Block extends Template_Block {
    function content() {
        $block_template = $this->PMDR->getNew('Template',dirname(__FILE__).'/events_calendar_block.tpl');
        $block_template->cache_id = 'block_events_calendar';
        $block_template->expire = 900;
        return $block_template;
    }
}
?>