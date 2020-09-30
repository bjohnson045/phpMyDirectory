<?php
/**
 * Strings::trim
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _trim($str, $charlist = FALSE) {
    if($charlist === FALSE) {
        return trim($str);
    }
    return Strings::ltrim(Strings::rtrim($str,$charlist),$charlist);
}
?>