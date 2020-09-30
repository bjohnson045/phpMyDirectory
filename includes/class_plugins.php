<?php
/**
* Class Plugin
* Used to handle code plugins to run code in variable places throughout the script
*/
class Plugins {
    /**
    * Registry object
    * @var object
    */
    var $PMDR;
    /**
    * Database object
    * @var object
    */
    var $db;
    /**
    * Plugins
    * @var array
    */
    var $plugins = array();
    /**
    * Hooks
    * @var array
    */
    var $hooks = array();
    /**
    * Control panel menu
    * @var array
    */
    var $admin_menu = array();

    /**
    * Plugin Constructor
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('DB');
        $this->loadPlugins();
    }

    /**
    * Load Plugins
    * @return void
    */
    private function loadPlugins() {
        if(is_null($plugins = $this->PMDR->get('Cache')->get('plugins'))) {
            // We check here since this may be an upgrade where the plugins table was old
            if(@$plugins = $this->db->GetCol("SELECT id FROM ".T_PLUGINS." WHERE installed=1 AND active=1")) {
                $this->plugins = $plugins;
            } else {
                $plugins = array();
            }
            $this->PMDR->get('Cache')->write('plugins',$plugins);
        } else {
            $this->plugins = $plugins;
        }
    }

    /**
    * Add a hook
    * @param string $hook Hook name
    * @param string $function Function name
    * @param int $priority Priority/run order
    */
    public function add_hook($hook,$function,$priority=0) {
        $this->hooks[$hook][$priority][] = $function;
    }

    /**
    * Run a hook
    * Input variables are dependent on the hook function
    * @return void
    */
    public function run_hook() {
        $variables = func_get_args();
        if(isset($this->hooks[$variables[0]])) {
            $hooks = $this->hooks[$variables[0]];
            ksort($hooks);

            foreach($hooks AS $priority=>$functions) {
                foreach($functions AS $function) {
                    // TODO: We may want to do something here eventually.
                    //       Possibly abort execution if false is explicitly returned.
                    $return = call_user_func($function,array_slice($variables,1));
                }
            }
        }
    }

    /**
    * Add admin menu
    * @param string $key Menu key
    * @param string $page_title Page title
    * @param string $menu_text Menu text
    * @param string $target Target
    * @param int $priority Priority
    */
    public function add_admin_menu($key,$page_title,$menu_text,$target,$priority=0) {
        $this->admin_menu[$key]['menu'] = array('page_title'=>$page_title,'menu_text'=>$menu_text,'target'=>$target,'priority'=>$priority);
    }

    /**
    * Add admin submenu
    * @param string $key Menu key
    * @param string $page_title Page title
    * @param string $menu_text Menu text
    * @param string $target Target
    * @param int $priority Priority
    */
    public function add_admin_submenu($key,$page_title,$menu_text,$target,$priority=0) {
        $this->admin_menu[$key]['submenu'][] = array('page_title'=>$page_title,'menu_text'=>$menu_text,'target'=>$target,'priority'=>$priority);
    }

    /**
    * Enable a plugin
    * @param string $id Plugin ID
    * @return void
    */
    public function enable($id) {
        $this->db->Execute("UPDATE ".T_PLUGINS." SET active=1 WHERE id=?",array($id));
        $this->PMDR->get('Cache')->delete('plugins');
    }

    /**
    * Disable a plugin
    * @param string $id Plugin ID
    * @return void
    */
    public function disable($id) {
        $this->db->Execute("UPDATE ".T_PLUGINS." SET active=0 WHERE id=?",array($id));
        $this->PMDR->get('Cache')->delete('plugins');
    }

    /**
    * Install a plugin
    * @param string $id Plugin ID
    * @return bool
    */
    public function install($id) {
        $PMDR = $this->PMDR;
        $db = $this->db;
        $this->PMDR->get('Cache')->delete('plugins');
        $plugin = $this->db->GetRow("SELECT * FROM ".T_PLUGINS." WHERE id=?",array($id));
        $this->db->Execute("UPDATE ".T_PLUGINS." SET installed=1, active=1 WHERE id=?",array($plugin['id']));
        if(file_exists(PLUGINS_PATH.$plugin['id'].'/'.$plugin['id'].'_install.php')) {
            if(!@eval('return true;?>'.file_get_contents(PMDROOT.'/modules/plugins/'.$plugin['id'].'/'.$plugin['id'].'_install.php'))) {
                $this->db->Execute("UPDATE ".T_PLUGINS." SET installed=0 WHERE id=?",array($plugin['id']));
                return false;
            } else {
                include(PMDROOT.'/modules/plugins/'.$plugin['id'].'/'.$plugin['id'].'_install.php');
                return true;
            }
        } else {
            return true;
        }
    }

    /**
    * Uninstall a plugin
    * @param string $id Plugin ID
    * @return bool
    */
    public function uninstall($id) {
        $PMDR = $this->PMDR;
        $db = $this->db;
        $this->PMDR->get('Cache')->delete('plugins');
        $plugin = $this->db->GetRow("SELECT * FROM ".T_PLUGINS." WHERE id=?",array($id));
        $this->db->Execute("UPDATE ".T_PLUGINS." SET installed=0, active=0 WHERE id=?",array($plugin['id']));
        if(file_exists(PLUGINS_PATH.$plugin['id'].'/'.$plugin['id'].'_uninstall.php')) {
            if(!@eval('return true;?>'.file_get_contents(PMDROOT.'/modules/plugins/'.$plugin['id'].'/'.$plugin['id'].'_uninstall.php'))) {
                $this->db->Execute("UPDATE ".T_PLUGINS." SET installed=1 WHERE id=?",array($plugin['id']));
                return false;
            } else {
                include(PMDROOT.'/modules/plugins/'.$plugin['id'].'/'.$plugin['id'].'_uninstall.php');
                return true;
            }
        } else {
            return true;
        }
    }
}
?>