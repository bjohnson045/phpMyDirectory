<?php
/**
* Class Languages
* Handles languages in teh software allowing for a master language and sublanguages overriding the master
*/
class Languages {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Languages Constructor
    * @param object $PMDR Registry
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Insert language
    * @param array $data Language data
    * @return integer Language ID
    */
    function insert($data) {
        $insert_array = array($data['title'],$data['languagecode'],$data['charset'], $data['textdirection'], $data['decimalseperator'], $data['thousandseperator'], $data['decimalplaces'],  $data['currency_prefix'], $data['currency_suffix'], $data['date_override'], $data['time_override'], $data['locale'], $data['active']);
        $this->db->Execute("INSERT INTO ".T_LANGUAGES." (title, languagecode, charset, textdirection, decimalseperator, thousandseperator, decimalplaces, currency_prefix, currency_suffix, date_override, time_override, locale, active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",$insert_array);
        $languageid = $this->db->Insert_ID();
        $this->db->Execute("ALTER TABLE ".T_CATEGORIES." ADD title_$languageid varchar(255) NULL DEFAULT NULL");
        $this->db->Execute("ALTER TABLE ".T_MENU_LINKS." ADD title_$languageid varchar(255) NULL DEFAULT NULL");
        if(isset($data['phrase_csv']['tmp_name']) AND $data['phrase_csv']['tmp_name'] != '') {
            @ini_set('auto_detect_line_endings', true);
            $csv = fopen($data['phrase_csv']['tmp_name'],'r');
            $csv_line = fgetcsv($csv, 0,',','"'); // get rid of the first line
            while($csv_line = fgetcsv($csv, 0,',','"')) {
                $this->db->Execute("INSERT INTO ".T_LANGUAGE_PHRASES." (languageid, variablename, content, section) VALUES (?,?,?,?)",array($languageid,$csv_line[1],(isset($csv_line[3]) AND $csv_line[3] != '') ? $csv_line[3] : $csv_line[2],$csv_line[0]));
            }
            fclose($csv);
        }
        $this->PMDR->get('Cache_Fallback')->delete('language_codes','language_');
        return $languageid;
    }

    /**
    * Update language
    * @param integer $id Language ID
    * @param array $data Language Data
    * @return void
    */
    function update($id, $data) {
        $update_array = array($data['title'],$data['languagecode'],$data['charset'], $data['textdirection'], $data['decimalseperator'], $data['thousandseperator'],  $data['decimalplaces'], $data['currency_prefix'], $data['currency_suffix'], $data['date_override'], $data['time_override'], $data['locale'], $data['active'], $id);
        $this->db->Execute("UPDATE ".T_LANGUAGES." SET title=?, languagecode=?, charset=?, textdirection=?, decimalseperator=?, thousandseperator=?, decimalplaces=?, currency_prefix=?, currency_suffix=?, date_override=?, time_override=?, locale=?, active=? WHERE languageid=?",$update_array);

        if($_FILES['phrase_csv']['tmp_name'] != '') {
            @ini_set('auto_detect_line_endings', true);
            $csv = fopen($_FILES['phrase_csv']['tmp_name'],'r');
            $csv_line = fgetcsv($csv, 0,',','"'); // get rid of the first line
            while($csv_line = fgetcsv($csv, 0,',','"')) {
                $content = (isset($csv_line[3]) AND $csv_line[3] != '') ? $csv_line[3] : $csv_line[2];
                $this->db->Execute("INSERT INTO ".T_LANGUAGE_PHRASES." (languageid,variablename,section,content) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE content=?",array($id,$csv_line[1],$csv_line[0],$content,$content));
            }
            unset($content);
            fclose($csv);
        }

        $this->PMDR->get('Cache_Fallback')->delete('language_codes','language_');
    }

    /**
    * Export language
    * Serves a CSV file to the browser with all language phrases
    * @param integer $id Language Id
    * @return void
    */
    function export($id, $auto_translate = null) {
        $csv_output = "\"Section\",\"Variable Name\",\"Content\"";
        if($id!=-1) {
            $phrases = $this->db->GetAll("SELECT master.section, master.variablename, master.content, phrases.content AS content_translated
            FROM ".T_LANGUAGE_PHRASES." as master LEFT JOIN ".T_LANGUAGE_PHRASES." as phrases ON master.variablename=phrases.variablename AND master.section=phrases.section AND phrases.languageid=?
            WHERE master.languageid=-1",array($id));
            $csv_output .= ",\"Content Translated\"";
        } else {
            $phrases = $this->db->GetAll("SELECT section, variablename, content FROM ".T_LANGUAGE_PHRASES." WHERE languageid=?",array($id));
        }
        $csv_output .= "\r\n";
        foreach($phrases as $phrase) {
            $csv_output .= '"'.$phrase['section'].'","'.$phrase['variablename'].'","'.str_replace('"','""',$phrase['content']).'"';
            if($id!=-1) {
                $csv_output .= ',"'.str_replace('"','""',$phrase['content_translated']).'"';
            }
            $csv_output .= "\r\n";
        }
        $serve = $this->PMDR->get('ServeFile');
        if($language = $this->find($id)) {
            $serve->serve($language['title'].'.csv',$csv_output);
        } else {
            $serve->serve('Language.csv',$csv_output);
        }
    }

    /**
    * Find language
    * @param integer $id Language ID
    * @return array Language array
    */
    function find($id) {
        return $this->db->GetRow("SELECT * FROM ".T_LANGUAGES." WHERE languageid=?",array($id));
    }

    /**
    * Delete Language
    * Delets the language and all corresponding phrases
    * @param integer $id Language ID
    * @return void
    */
    function delete($id) {
        $this->db->Execute("DELETE FROM ".T_LANGUAGES." WHERE languageid=?",array($id));
        $this->db->Execute("DELETE FROM ".T_LANGUAGE_PHRASES." WHERE languageid=?",array($id));
        $this->db->DropColumn(T_CATEGORIES,'title_'.$id);
        $this->db->DropColumn(T_MENU_LINKS,'title_'.$id);
        $this->PMDR->get('Cache_Fallback')->delete('language_codes','language_');
    }

    /**
    * Get all languages
    * @return array Multi dimensional array of languages
    */
    function getAll() {
        return $this->db->GetAll("SELECT * FROM ".T_LANGUAGES);
    }

    /**
    * Get the language by the language code.
    * @param string $code
    * @return int|boolean Language ID or false if not found
    */
    function getByLanguageCode($code) {
        if(is_null($language_codes = $this->PMDR->get('Cache_Fallback')->get('language_codes', 0, 'language_'))) {
            $language_codes = $this->db->GetAssoc("SELECT languagecode, languageid FROM ".T_LANGUAGES);
            $this->PMDR->get('Cache_Fallback')->write('language_codes',$language_codes,'language_');
        }
        if(isset($language_codes[$code])) {
            return $language_codes[$code];
        } else {
            return false;
        }
    }

    /**
    * Get a language field name
    * Used for multilanguage data to get the first non-null field value int he database.
    * @param string $default Field name
    * @param string $prefix Field prefix, example: "t."
    */
    function getFieldName($default,$prefix='') {
        if($this->PMDR->getLanguage('languageid') != 1) {
            return 'COALESCE(NULLIF('.$prefix.$default.'_'.$this->PMDR->getLanguage('languageid').',\'\'),'.$prefix.$default.') AS '.$default;
        } else {
            return $prefix.$default;
        }
    }
}

/**
* Class Phrases
* Language phrases which make up a language set
*/
class Phrases {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Phrases Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Insert phrase
    * @param array $data Phrase data
    * @return integer Phrase ID
    */
    function insert($data, $section='custom') {
        // The insert is ignored in case we have duplicates.
        $this->db->Execute("INSERT IGNORE INTO ".T_LANGUAGE_PHRASES." (languageid, variablename, content, section) VALUES (-1,?,?,?)",array($data['variablename'],$data['content'],$section));
        $this->PMDR->get('Cache')->deletePrefix('language');
        return $this->db->Insert_ID();
    }

    /**
    * Update phrase by variable name, used for updating multiple languages at once
    * @param string $id Phrase variable name
    * @param array $data Phrase data
    * @return void
    */
    function update($id, $section, $data) {
        foreach($data as $languageid=>$value) {
            $exists = $this->db->GetRow("SELECT * FROM ".T_LANGUAGE_PHRASES." WHERE languageid=? AND variablename=? AND section=?",array($languageid,$id,$section));
            if($exists) {
                if($value == '') {
                    $this->db->GetRow("DELETE FROM ".T_LANGUAGE_PHRASES." WHERE languageid=? AND variablename=? AND section=?",array($languageid,$id,$section));
                } else {
                    $this->db->Execute("UPDATE ".T_LANGUAGE_PHRASES." SET content=? WHERE languageid=? AND variablename=? AND section=?",array($value,$languageid,$id,$section));
                }
            } elseif($value != '') {
                $this->db->Execute("INSERT INTO ".T_LANGUAGE_PHRASES." (content,languageid,section,variablename) VALUES (?,?,?,?)",array($value,$languageid,$section,$id));
            }
        }
        $this->clearUpdated($section,$id);
        $this->PMDR->get('Cache')->deletePrefix('language');
    }

    /**
    * Update single phrase
    * @param integer $languageid Language Id
    * @param string $variable Phrase variable name
    * @param string $content Content for phrase
    * @return void
    */
    function updatePhrase($languageid, $section, $variable, $content, $allow_empty = false) {
        $exists = $this->db->GetRow("SELECT * FROM ".T_LANGUAGE_PHRASES." WHERE languageid=? AND variablename=? AND section=?",array($languageid,$variable,$section));
        if($exists) {
            if($content == '' AND !$allow_empty) {
                $this->db->GetRow("DELETE FROM ".T_LANGUAGE_PHRASES." WHERE languageid=? AND variablename=? AND section=?",array($languageid,$variable,$section));
            } else {
                $this->db->Execute("UPDATE ".T_LANGUAGE_PHRASES." SET content=? WHERE languageid=? AND variablename=? AND section=?",array($content,$languageid,$variable,$section));
            }
        } elseif($content != '' OR $allow_empty) {
            $this->db->Execute("INSERT INTO ".T_LANGUAGE_PHRASES." (content,section,languageid,variablename) VALUES (?,?,?,?)",array($content,$section,$languageid,$variable));
        }
        $this->clearUpdated($section,$variable);
        $this->PMDR->get('Cache')->deletePrefix('language');
    }

    /**
    * Update multiple phrases for a single language
    * @param array $data Phrase data
    * @param string $id Phrase variable name
    * @return void
    */
    function multiUpdate($data, $id) {
        foreach($data['phrases'] as $section=>$phrases) {
            foreach($phrases AS $variable=>$phrase) {
                $exists = $this->db->GetRow("SELECT * FROM ".T_LANGUAGE_PHRASES." WHERE languageid=? AND variablename=? AND section=?",array($id,$variable,$section));
                if($exists) {
                    if($phrase != '') {
                        $this->db->Execute("UPDATE ".T_LANGUAGE_PHRASES." SET content=? WHERE languageid=? AND variablename=? AND section=?",array($phrase,$id,$variable,$section));
                    } else {
                        $this->db->Execute("DELETE FROM ".T_LANGUAGE_PHRASES." WHERE languageid=? AND variablename=? AND section=?",array($id,$variable,$section));
                    }
                } elseif($phrase != '') {
                    $this->db->Execute("INSERT INTO ".T_LANGUAGE_PHRASES." (content,languageid,variablename,section) VALUES (?,?,?,?)",array($phrase,$id,$variable,$section));
                }
                $this->clearUpdated($section,$variable);
            }
        }
        $this->PMDR->get('Cache')->deletePrefix('language');
    }

    function clearUpdated($section, $variablename) {
        $this->db->Execute("UPDATE ".T_LANGUAGE_PHRASES." SET updated=0 WHERE section=? AND variablename=?",array($section,$variablename));
    }

    /**
    * Find phrase
    * @param integer $id Phrase ID
    * @return array Phrase data
    */
    function find($id) {
        return $this->db->GetRow("SELECT * FROM ".T_LANGUAGE_PHRASES." WHERE phraseid=? ORDER BY languageid",array($id));
    }

    /**
    * Delete Phrase
    * @param string $id Variable Name
    * @param string $section Section
    * @return void
    */
    function delete($id, $section) {
        $this->db->Execute("DELETE FROM ".T_LANGUAGE_PHRASES." WHERE variablename=? AND section=?",array($id,$section));
    }

    /**
    * Get all phrases
    * @return array All phrases
    */
    function getAll() {
        return $this->db->GetAll("SELECT * FROM ".T_LANGUAGE_PHRASES." ORDER BY languageid");
    }

    /**
    * Get Master phrases
    * @return array Language phrases
    */
    function getMaster() {
        return $this->getByLanguage(-1);
    }

    /**
    * Get all phrases belonging to a language
    * @param integer $id Language Id
    * @return array Phrases array
    */
    function getByLanguage($id) {
        return $this->db->GetAll("SELECT * FROM ".T_LANGUAGE_PHRASES." WHERE languageid = ?", array($id));
    }
}
?>