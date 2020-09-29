<?php

/**
 * No password authentication method. This is used, for example, when connecting with FB
 * and no password is set.
 *
 * @package XenForo_Authentication
 */
class XenForo_Authentication_NoPassword extends XenForo_Authentication_Abstract
{
	/**
	* Initialize data for the authentication object.
	*
	* @param string   Binary data from the database
	*/
	public function setData($data)
	{
	}

	/**
	 * @see XenForo_Authentication_Abstract::isUpgradable()
	 */
	public function isUpgradable()
	{
		return false;
	}

	/**
	* Generate new authentication data
	* @see XenForo_Authentication_Abstract::generate()
	*/
	public function generate($password)
	{
		return serialize(array());
	}

	/**
	* Authenticate against the given password
	* @see XenForo_Authentication_Abstract::authenticate()
	*/
	public function authenticate($userId, $password)
	{
		return false;
	}

	/**
	 * Determines if auth method provides password.
	 * @see XenForo_Authentication_Abstract::hasPassword()
	 */
	public function hasPassword()
	{
		return false;
	}
}