<?php
/**
 * Strings::strcasecmp
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strcasecmp($strX, $strY) {
    $strX = Strings::strtolower($strX);
    $strY = Strings::strtolower($strY);
    return strcmp($strX, $strY);
}
?>