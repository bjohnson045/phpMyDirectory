<?php
/**
* Class Templates Data
*/
class Templates_Data extends TableGateway {
    /**
    * Registry
    * @var object Registry
    */
    var $PMDR;
    /**
    * Database
    * @var object Database
    */
    var $db;

    /**
    * Templates Data Constructor
    * @param object $PMDR
    * @return Templates_Data
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_TEMPLATES_DATA;
    }

    /**
    * Update template file
    * @param array $data
    * @param int $id
    */
    function update($data, $id) {
        $template = $this->db->GetRow("SELECT t.folder, td.name, td.subfolder FROM ".T_TEMPLATES_DATA." td, ".T_TEMPLATES." t WHERE td.template_id = t.id AND td.id = ?",array($id));
        file_put_contents(PMDROOT.'/template/'.$template['folder'].($template['subfolder'] != '' ? '/'.$template['subfolder'] : '').'/'.$template['name'],$data['contents']);
        $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();
        return parent::update($data, $id);
    }

    /**
    * Delete template file
    * @param int $id
    */
    function delete($id) {
        $template = $this->db->GetRow("SELECT t.folder, td.name, td.subfolder FROM ".T_TEMPLATES_DATA." td, ".T_TEMPLATES." t WHERE td.template_id = t.id AND td.id = ?",array($id));
        @unlink(PMDROOT.'/template/'.$template['folder'].($template['subfolder'] != '' ? '/'.$template['subfolder'] : '').'/'.$template['name']);
        parent::delete($id);
    }

    /**
    * Revert a template file
    * @param int $id
    */
    function revert($id) {
        $revert_data = $this->db->GetRow("SELECT contents_default AS contents FROM ".T_TEMPLATES_DATA." WHERE id=?",array($id));
        return $this->update($revert_data,$id);
    }
}
?>