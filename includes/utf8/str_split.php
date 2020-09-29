<?php
/**
 * Strings::str_split
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _str_split($str, $split_len = 1) {
    if(!preg_match('/^[0-9]+$/',$split_len) || $split_len < 1) {
        return false;
    }
    $len = Strings::strlen($str);
    if($len <= $split_len) {
        return array($str);
    }
    preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $str, $ar);
    return $ar[0];
}
?>