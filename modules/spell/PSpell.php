<?php
/**
* Class Spell_PSpell
* Checks the spelling of words and offers suggestions
* Wrapper for PHP pspell extension - extension required
*/
class Spell_PSpell {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * PSpell link
    * @var PSpell
    */
    var $pspell;
    /**
    * @var pspell config
    */
    var $pspell_config;
    /**
    * @var integer Minimum word length
    */
    var $word_minimum = 3;
    /**
    * @var integer Number of suggestions
    */
    var $suggestions_mode = 'SLOW';
    /**
    * @var string Path to language data files
    */
    var $language_data_path = null;
    /**
    * @var string Path to main word list
    */
    var $word_list_path = null;
    /**
    * PSpell Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->pspell_config = pspell_config_create($this->PMDR->getLanguage('languagecode'), null, null, 'utf-8');
        $this->setMinimumWordLength($this->word_minimum);
        $this->setSuggestionMode($this->suggestions_mode);
    }

    /**
    * Set suggestion mode
    * @param string $mode FAST, MEDIUM, SLOW
    * @return void
    */
    function setSuggestionMode($mode) {
        switch ($mode) {
           case 'FAST':
             pspell_config_mode($this->pspell_config, PSPELL_FAST);
             break;
           case 'MEDIUM':
             pspell_config_mode($this->pspell_config, PSPELL_NORMAL);
             break;
           case 'SLOW':
             pspell_config_mode($this->pspell_config, PSPELL_BAD_SPELLERS);
             break;
        }
        $this->reloadConfig();
    }
    /**
    * Set minmum word length
    * @param int $length Minimum length
    * @return void
    */
    function setMinimumWordLength($length) {
        pspell_config_ignore($this->pspell_config, $length);
        $this->reloadConfig();
    }

    /**
    * Set language data path
    * If the server does not have the language data we can set the path
    * @param string $path Path to data
    * @return void
    */
    function setLanguageDataPath($path) {
        pspell_config_data_dir($this->pspell_config, $path);
        $this->reloadConfig();
    }

    /**
    * Set word list path
    * If the server does not have a word list we can set a custom path
    * @param string $path Path to word list
    * @return void
    */
    function setWordListPath($path) {
        pspell_config_dict_dir($this->pspell_config, $path);
        $this->reloadConfig();
    }

    /**
    * Reload pspell configuration
    * @return void
    */
    function reloadConfig() {
        $this->pspell = pspell_new_config($this->pspell_config);
    }

    function checkSpelling($word) {
        return pspell_check($this->pspell, $word);
    }

    /**
    * Check the spelling of a word(s)
    * @param mixed $word Word, or array of words
    */
    function getSuggestions($word) {
        return pspell_suggest($this->pspell, $word);
    }

    /**
    * Get suggestions of spelling for a word
    * @param mixed $word Word or array of words
    */
    function getSuggested($query) {
        $words = preg_split('/[\W]+/',$query);
        $new_suggestion = $query;
        foreach($words AS $word) {
            $suggestions = $this->getSuggestions($word);
            $new_suggestion = str_replace($word,$suggestions[0],$new_suggestion);
        }

        if($new_suggestion == $query) {
            return false;
        }

        return $new_suggestion;
    }
}
?>