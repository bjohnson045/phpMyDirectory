<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

abstract class lmcValidationInterface
{
	// does the validation
	abstract public function ValidateTransaction( $persistentData, $response );

	// returns an LMEC code for pass or fail
	abstract public function GetValidationCode();

	// returns an LMRC_ code to indicate info about the failure, if there was one
	abstract public function GetResponseType();

	// returns an id unique to this transaction stored in the persistent data
	abstract public function GetTransactionID( $persistentData );
}