<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

// we only want to redefine the client version of this class if we haven't already defined the non-client version
if(defined("LMRC_TESTCODE"))
{
	return;
}

define("LMRC_TESTCODE", 9999);

define("LMRC_HUMAN", 0);
define("LMRC_SUSPICIOUS", 1);



