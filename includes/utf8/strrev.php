<?php
/**
 * Strings::strrev
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strrev($str) {
    preg_match_all('/./us', $str, $matches);
    return implode('', array_reverse($matches[0]));
}
?>