<?php
/**
 * Strings::ucwords
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _ucwords($str) {
    $pattern = '/(^|([\x0c\x09\x0b\x0a\x0d\x20]+))([^\x0c\x09\x0b\x0a\x0d\x20]{1})[^\x0c\x09\x0b\x0a\x0d\x20]*/u';
    return preg_replace_callback($pattern,'_ucwords_callback',$str);
}

function _ucwords_callback($matches) {
    $leadingws = $matches[2];
    $ucfirst = Strings::strtoupper($matches[3]);
    $ucword = Strings::substr_replace(Strings::ltrim($matches[0]),$ucfirst,0,1);
    return $leadingws.$ucword;
}
?>