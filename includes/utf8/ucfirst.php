<?php
/**
 * Strings::ucfirst
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _ucfirst($str){
    switch(_strlen($str)) {
        case 0:
            return '';
        break;
        case 1:
            return _strtoupper($str);
        break;
        default:
            preg_match('/^(.{1})(.*)$/us', $str, $matches);
            return _strtoupper($matches[1]).$matches[2];
        break;
    }
}
?>