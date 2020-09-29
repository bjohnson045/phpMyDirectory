<?php
/**
 * Strings::substr_replace
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _substr_replace($str, $repl, $start, $length = NULL ) {
    preg_match_all('/./us', $str, $ar);
    preg_match_all('/./us', $repl, $rar);
    if($length === NULL) {
        $length = Strings::strlen($str);
    }
    array_splice($ar[0], $start, $length, $rar[0]);
    return implode('',$ar[0]);
}
?>