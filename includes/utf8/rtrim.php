<?php
/**
 * Strings::rtrim
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _rtrim($str, $charlist = false) {
    if($charlist === false) {
        return rtrim($str);
    }
    $charlist = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$charlist);
    return preg_replace('/['.$charlist.']+$/u','',$str);
}
?>