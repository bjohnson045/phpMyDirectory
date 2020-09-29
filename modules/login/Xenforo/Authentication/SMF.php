<?php

/**
 * SMF authentication method.
 *
 * @package XenForo_Authentication
 */
class XenForo_Authentication_SMF extends XenForo_Authentication_Abstract
{
	/**
	 * Password info for this authentication object
	 *
	 * @var array
	 */
	protected $_data = array();

	protected function _createHash($password, $username)
	{
		return sha1($username . utf8_unhtml($password));
	}

	/**
	 * Initialize data for the authentication object.
	 *
	 * @param string   Binary data from the database
	 */
	public function setData($data)
	{
		$this->_data = unserialize($data);
	}

	/**
	 * Generate new authentication data
	 * @see XenForo_Authentication_Abstract::generate()
	 */
	public function generate($password)
	{
		throw new XenForo_Exception('Cannot generate authentication for this type.');
	}

	/**
	 * Authenticate against the given password
	 * @see XenForo_Authentication_Abstract::authenticate()
	 */
	public function authenticate($userId, $password)
	{
		if (!is_string($password) || $password === '' || empty($this->_data))
		{
			return false;
		}

		$userHash = $this->_createHash($password, $this->_data['username']);
		return ($userHash === $this->_data['hash']);
	}
}