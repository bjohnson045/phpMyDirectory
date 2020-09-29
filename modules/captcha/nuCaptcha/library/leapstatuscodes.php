<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

// we only want to redefine the client version of this class if we haven't already defined the non-client version
if(defined("LMSC_TESTCODE"))
{
	return;
}

define("LMSC_OK", 10);
define("LMSC_ERROR", 11);
define("LMSC_UNKNOWN", 12);
define("LMSC_UNDEFINED", 13);
define("LMSC_INVALIDDATA", 14);
define("LMSC_INVALIDTYPE", 15);

define("LMSC_TEST", 23); // For testing only


// Token Server Response (1000-1099)
define("LMSC_PUBLISHER_DISABLED", 1002);

// Data Server Response (1100-1199)

// Validation Server Response (1200-1299)
define("LMSC_CORRECT", 1200);			// Correct Response
define("LMSC_WRONG", 1201);				// Wrong Response
define("LMSC_EMPTY", 1202);				// No Response

// Leap Marketing Client Library (PHP) (1300-1399)
define("LMSC_NOTRANSACTION", 1300);		// No Transaction.  InitializeTransaction not called
define("LMSC_INVALIDTRES", 1301);		// The Token Response was invalid
define("LMSC_INVALIDVRES", 1303);		// The Validation Response was invalid
define("LMSC_INVALIDPERSISTENT", 1304);	// The persistent data was invalid
define("LMSC_INVALIDVERSION", 1305);    // invalid version used in encyphered message
define("LMSC_COULDNOTCONNECT", 1306);   // could not connect to a server
define("LMSC_INVALIDIVLENGTH", 1308);    // invalid iv length used in encyphered message
define("LMSC_INVALIDKEY", 1310);        // invalid key
define("LMSC_INVALIDCONFIGFILE", 1313); 	// invalid config file on client machine
define("LMSC_CLIENTKEYNOTSET", 1316);		// client key not initialized

define("LMSC_DNSERROR", 1323);				// An error occured with DNS lookup

// *** Leap Library (PHP) (10000-19999)
// Communication (10000-10099)
define("LMSC_UNDEFINEDCHUNK", 10000);


// legacy LMEC codes
if(defined("LMEC_TESTCODE"))
{
	return;
}

define("LMEC_OK", 10);
define("LMEC_ERROR", 11);
define("LMEC_UNKNOWN", 12);
define("LMEC_UNDEFINED", 13);
define("LMEC_INVALIDDATA", 14);
define("LMEC_INVALIDTYPE", 15);

define("LMEC_TEST", 23); // For testing only


// Token Server Response (1000-1099)
define("LMEC_PUBLISHER_DISABLED", 1002);

// Data Server Response (1100-1199)

// Validation Server Response (1200-1299)
define("LMEC_CORRECT", 1200);			// Correct Response
define("LMEC_WRONG", 1201);				// Wrong Response
define("LMEC_EMPTY", 1202);				// No Response

// Leap Marketing Client Library (PHP) (1300-1399)
define("LMEC_NOTRANSACTION", 1300);		// No Transaction.  InitializeTransaction not called
define("LMEC_INVALIDTRES", 1301);		// The Token Response was invalid
define("LMEC_INVALIDVRES", 1303);		// The Validation Response was invalid
define("LMEC_INVALIDPERSISTENT", 1304);	// The persistent data was invalid
define("LMEC_INVALIDVERSION", 1305);    // invalid version used in encyphered message
define("LMEC_COULDNOTCONNECT", 1306);   // could not connect to a server
define("LMEC_INVALIDIVLENGTH", 1308);    // invalid iv length used in encyphered message
define("LMEC_INVALIDKEY", 1310);        // invalid key
define("LMEC_INVALIDCONFIGFILE", 1313); 	// invalid config file on client machine
define("LMEC_CLIENTKEYNOTSET", 1316);		// client key not initialized

define("LMEC_DNSERROR", 1323);				// An error occured with DNS lookup

// *** Leap Library (PHP) (10000-19999)
// Communication (10000-10099)
define("LMEC_UNDEFINEDCHUNK", 10000);
