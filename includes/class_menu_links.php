<?php
/**
* Menu Links class
* Used for construction of the menu.  Used in connection with custom pages.
*/
class CustomLinks extends TableGateway{
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;
    /**
    * CustomLinks constructor
    * @param object Registry
    */
    function __construct($PMDR) {
        $this->table = T_MENU_LINKS;
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->template = PMDROOT.TEMPLATE_PATH.'blocks/menu.tpl';
        $this->item_template = PMDROOT.TEMPLATE_PATH.'blocks/menu_item.tpl';
        $this->title_field = $this->PMDR->get('Languages')->getFieldName('title','m.');
    }

    /**
    * Calculate the levels from the menu array
    * @param array $links Links
    * @param int $level Current level
    * @return array Links after levels have been calculated
    */
    private function calculateLevels(&$links, $level = 0) {
        foreach($links AS $key=>$link) {
            $links[$key]['level'] = $level;
            if(isset($link['children']) AND !empty($link['children']) AND is_array($link['children'])) {
                $this->calculateLevels($link['children'], $level+1);
            }
        }
        return $links;
    }

    /**
    * Convert flat link structure to a multidimensional array
    * @param array $links
    * @return array Links in multidimensional array format
    */
    private function convertLinksToArray($links) {
        $result = array();
        foreach($links AS $link) {
            if(is_null($link['parent_id'])) {
                $link['parent_id'] = 0;
            }
            isset($result[$link['parent_id']]) ?: $result[$link['parent_id']] = array();
            isset($result[$link['id']]) ?: $result[$link['id']] = array();
            $result[$link['parent_id']][] = array_merge($link, array('children'=>&$result[$link['id']]));
        }
        if(count($result) AND count($result[0])) {
            return $this->calculateLevels($result[0],0);
        } else {
            return array();
        }
    }

    /**
    * Convert a multidimensional menu array back to flat format
    * @param array $links Multidimensional array format
    * @param mixed $result Result array
    * @return array Results in flat format
    */
    private function convertToFlatUnordered($links, &$result = array()) {
        foreach($links AS $key=>$link) {
            if(isset($link['children']) AND !empty($link['children']) AND is_array($link['children'])) {
                $this->convertToFlatUnordered($link['children'],$result);
                $link['children'] = 1;
            }
            if($link['children'] != 1) {
                $link['children'] = 0;
            }
            $result[] = $link;
        }
        return $result;
    }

    /**
    * Convert links and order them
    * @param array $links Links
    * @return array Links flat and ordered
    */
    private function convertToFlat($links) {
        $links = $this->convertToFlatUnordered($links);
        uasort($links,function($a, $b) {
            if($a['level'] < $b['level']) {
                return -1;
            }
            if($a['ordering'] < $b['ordering']) {
                return -1;
            }
            return 1;
        });
        return $links;
    }

    /**
    * Get flat version of links.  Populates the level value and children flag
    * @param boolean $sitemap Get only links designated for the sitemap
    * @return array Links formatted flat
    */
    public function getLinksFlat($sitemap) {
        return $this->convertToFlat($this->getLinks($sitemap));
    }

    /**
    * Get menu links in multidimensional array format
    * @param boolean $sitemap Get only links designated for the sitemap
    * @return array Links in multidimensional array format
    */
    public function getLinks($sitemap=false,$sitemap_xml=false) {
        if($this->PMDR->get('Session')->get('user_id') != '') {
            $logged_query = 'm.logged_in=1';
        } else {
            $logged_query = 'm.logged_out=1';
        }
        $links = $this->db->GetAll("
            SELECT ".$this->title_field.", m.id, m.page_id, m.parent_id, m.link, m.target, m.nofollow, m.ordering, m.logged_in, m.logged_out, m.sitemap, m.active, m.sitemap_xml_priority,
                IF(m.page_id IS NOT NULL,
                    IF(".intval(MOD_REWRITE).",
                        CONCAT('".BASE_URL_NOSSL."/pages/',friendly_url,'.html'),
                        CONCAT('".BASE_URL_NOSSL."/page.php?id=',p.id)
                    ),
                    IF(INSTR(link,'://'),
                        link,
                        CONCAT('".BASE_URL."/',link)
                    )
                ) as url
            FROM  ".T_MENU_LINKS." m
                LEFT JOIN ".T_PAGES." p ON m.page_id=p.id
            WHERE
                m.active=1 AND ".($sitemap ? "m.sitemap=1 AND " : '').($sitemap_xml ? "m.sitemap_xml=1 AND " : '').$logged_query."
            ORDER BY m.ordering");
        return $this->convertLinksToArray($links);
    }

    /**
    * Get a menu segment, looping through all children levels
    * @param integer $parent Parent used to start root of returned menu
    * @param integer $level Used for indendation
    */
    private function getMenuLoop($links) {
        foreach($links as $link) {
            $template = $this->PMDR->getNew('Template');
            $template->set('id',$link['id']);
            $template->set('parent_id',$link['parent_id']);
            $template->set('active',$link['url'] == URL);
            $template->set('link',$link['url']);
            $template->set('link_title',$link['title']);
            $template->set('nofollow',($link['nofollow'] ? ' rel="nofollow"' : ''));
            $template->set('target',($link['target'] ? ' target="'.$link['target'].'"' : ''));
            $template->set('level',$link['level']);
            $template->set('indent',str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;',$link['level']));
            $sub_menu = '';
            if(!empty($link['children'])) {
                ob_start();
                $this->getMenuLoop($link['children']);
                $sub_menu = ob_get_clean();
                $menu_template = $this->PMDR->getNew('Template',$this->template);
                $menu_template->set('items', $sub_menu);
                $menu_template->set('parent_id',$link['id']);
                $menu_template->set('level', $link['level']+1);
                $sub_menu = $menu_template->render();
            }
            $template->set('sub_menu', $sub_menu);
            echo $template->render($this->item_template);
        }
    }

    /**
    * Get and capture the menu output
    * @param integer $parent Parent used to start root of returned menu
    * @param integer $level Used for indendation
    * @return string Menu HTML
    */
    public function getMenuHTML($sitemap = false) {
        $links = $this->getLinks($sitemap);
        ob_start();
        $this->getMenuLoop($links);
        $menu_items = ob_get_clean();
        $menu_template = $this->PMDR->getNew('Template',$this->template);
        $menu_template->set('items', $menu_items);
        $menu_template->set('links', $links);
        return $menu_template->render();
    }
}
?>