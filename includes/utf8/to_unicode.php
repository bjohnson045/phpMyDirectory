<?php
/**
 * Strings::to_unicode
 *
 * The Original Code is Mozilla Communicator client code.
 * The Initial Developer of the Original Code is Netscape Communications Corporation.
 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see <http://hsivonen.iki.fi/php-utf8/>
 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _to_unicode($str) {
    $mState = 0;     // cached expected number of octets after the current octet
                     // until the beginning of the next UTF8 character sequence
    $mUcs4  = 0;     // cached Unicode character
    $mBytes = 1;     // cached expected number of octets in the current sequence

    $out = array();

    $len = strlen($str);

    for($i = 0; $i < $len; $i++) {

        $in = ord($str{$i});

        if ( $mState == 0) {

            // When mState is zero we expect either a US-ASCII character or a
            // multi-octet sequence.
            if (0 == (0x80 & ($in))) {
                // US-ASCII, pass straight through.
                $out[] = $in;
                $mBytes = 1;

            } else if (0xC0 == (0xE0 & ($in))) {
                // First octet of 2 octet sequence
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x1F) << 6;
                $mState = 1;
                $mBytes = 2;

            } else if (0xE0 == (0xF0 & ($in))) {
                // First octet of 3 octet sequence
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x0F) << 12;
                $mState = 2;
                $mBytes = 3;

            } else if (0xF0 == (0xF8 & ($in))) {
                // First octet of 4 octet sequence
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x07) << 18;
                $mState = 3;
                $mBytes = 4;

            } else if (0xF8 == (0xFC & ($in))) {
                /* First octet of 5 octet sequence.
                *
                * This is illegal because the encoded codepoint must be either
                * (a) not the shortest form or
                * (b) outside the Unicode range of 0-0x10FFFF.
                * Rather than trying to resynchronize, we will carry on until the end
                * of the sequence and let the later error handling code catch it.
                */
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x03) << 24;
                $mState = 4;
                $mBytes = 5;

            } else if (0xFC == (0xFE & ($in))) {
                // First octet of 6 octet sequence, see comments for 5 octet sequence.
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 1) << 30;
                $mState = 5;
                $mBytes = 6;

            } else {
                /* Current octet is neither in the US-ASCII range nor a legal first
                 * octet of a multi-octet sequence.
                 */
                trigger_error(
                        'Strings::to_unicode: Illegal sequence identifier '.
                            'in UTF-8 at byte '.$i,
                        E_USER_WARNING
                    );
                return FALSE;

            }

        } else {

            // When mState is non-zero, we expect a continuation of the multi-octet
            // sequence
            if (0x80 == (0xC0 & ($in))) {

                // Legal continuation.
                $shift = ($mState - 1) * 6;
                $tmp = $in;
                $tmp = ($tmp & 0x0000003F) << $shift;
                $mUcs4 |= $tmp;

                /**
                * End of the multi-octet sequence. mUcs4 now contains the final
                * Unicode codepoint to be output
                */
                if (0 == --$mState) {

                    /*
                    * Check for illegal sequences and codepoints.
                    */
                    // From Unicode 3.1, non-shortest form is illegal
                    if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
                        ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
                        ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
                        (4 < $mBytes) ||
                        // From Unicode 3.2, surrogate characters are illegal
                        (($mUcs4 & 0xFFFFF800) == 0xD800) ||
                        // Codepoints outside the Unicode range are illegal
                        ($mUcs4 > 0x10FFFF)) {

                        trigger_error(
                                'Strings::to_unicode: Illegal sequence or codepoint '.
                                    'in UTF-8 at byte '.$i,
                                E_USER_WARNING
                            );

                        return FALSE;

                    }

                    if (0xFEFF != $mUcs4) {
                        // BOM is legal but we don't want to output it
                        $out[] = $mUcs4;
                    }

                    //initialize UTF8 cache
                    $mState = 0;
                    $mUcs4  = 0;
                    $mBytes = 1;
                }

            } else {
                /**
                *((0xC0 & (*in) != 0x80) && (mState != 0))
                * Incomplete multi-octet sequence.
                */
                trigger_error(
                        'Strings::to_unicode: Incomplete multi-octet '.
                        '   sequence in UTF-8 at byte '.$i,
                        E_USER_WARNING
                    );

                return FALSE;
            }
        }
    }
    return $out;
}
?>