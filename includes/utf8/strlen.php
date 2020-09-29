<?php
/**
 * Strings::strlen
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strlen($str) {
    return strlen(utf8_decode($str));
}
?>