<?php
/**
 * Strings::strrev
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strspn($str, $mask, $offset = NULL, $length = NULL) {
    if($str == '' OR $mask == '') {
        return 0;
    }

    if (Strings::is_ascii($str) AND Strings::is_ascii($mask))
        return ($offset === NULL) ? strspn($str, $mask) : (($length === NULL) ? strspn($str, $mask, $offset) : strspn($str, $mask, $offset, $length));

    if ($offset !== NULL OR $length !== NULL) {
        $str = Strings::substr($str, $offset, $length);
    }

    // Escape these characters:  - [ ] . : \ ^ /
    // The . and : are escaped to prevent possible warnings about POSIX regex elements
    $mask = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $mask);
    preg_match('/^[^'.$mask.']+/u', $str, $matches);

    return isset($matches[0]) ? Strings::strlen($matches[0]) : 0;
}
?>